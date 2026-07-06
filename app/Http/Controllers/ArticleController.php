<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ArticleController extends Controller
{
    public function index(): JsonResponse
    {
        $articles = Article::where('is_published', true)
            ->select('article_id', 'author_id', 'title', 'thumbnail_image', 'created_at', 'uuid')
            ->with('author:id,name')
            ->latest('created_at')
            ->get();

        return response()->json($articles);
    }

    public function store(Request $request): JsonResponse
    {
        $request->merge([
            'is_published' => filter_var($request->input('is_published', false), FILTER_VALIDATE_BOOLEAN),
        ]);

        $validator = Validator::make($request->all(), [
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'is_published' => ['boolean'],
            'full_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation failed.', 'errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();
        $data['author_id'] = $request->user()->id;
        $data['created_at'] = now();

        if ($request->hasFile('full_image')) {
            $data = $this->processImage($request->file('full_image'), $data);
        }

        $article = Article::create($data);

        return response()->json($article->load('author:id,name,email'), 201);
    }

    public function show(Article $article): JsonResponse
    {
        if (! $article->is_published) {
            return response()->json(['message' => 'Article not found.'], 404);
        }

        return response()->json($article->load('author:id,name,email'));
    }

    public function update(Request $request, Article $article): JsonResponse
    {
        $request->merge([
            'is_published' => filter_var($request->input('is_published', $article->is_published), FILTER_VALIDATE_BOOLEAN),
        ]);

        $validator = Validator::make($request->all(), [
            'title' => ['sometimes', 'string', 'max:255'],
            'content' => ['sometimes', 'string'],
            'is_published' => ['boolean'],
            'full_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation failed.', 'errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();

        if ($request->hasFile('full_image')) {
            $this->deleteArticleImages($article);
            $data = $this->processImage($request->file('full_image'), $data);
        }

        $article->update($data);

        return response()->json($article->load('author:id,name,email'));
    }

    public function destroy(Article $article): JsonResponse
    {
        $this->deleteArticleImages($article);
        $article->delete();

        return response()->json(['message' => 'Article deleted.']);
    }

    private function deleteArticleImages(Article $article): void
    {
        if ($article->thumbnail_image) {
            Storage::disk('public')->delete($article->thumbnail_image);
        }

        if ($article->full_image) {
            Storage::disk('public')->delete($article->full_image);
        }
    }

    /**
     * @param  UploadedFile  $file
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function processImage(mixed $file, array $data): array
    {
        $disk = Storage::disk('public');
        $disk->makeDirectory('articles');

        $sourceImage = imagecreatefromstring($file->getContent());

        if (! $sourceImage) {
            return $data;
        }

        $origWidth = imagesx($sourceImage);
        $origHeight = imagesy($sourceImage);

        // Full image: compress to webp
        $fullName = 'articles/'.Str::random(8).'_'.time().'.webp';
        $fullPath = Storage::disk('public')->path($fullName);
        imagewebp($sourceImage, $fullPath, 80);

        // Thumbnail: 20% resolution
        $thumbWidth = max(1, (int) ($origWidth * 0.2));
        $thumbHeight = max(1, (int) ($origHeight * 0.2));
        $thumbImage = imagescale($sourceImage, $thumbWidth, $thumbHeight);

        $thumbName = 'articles/'.Str::random(8).'_'.time().'.webp';
        $thumbPath = Storage::disk('public')->path($thumbName);
        imagewebp($thumbImage, $thumbPath, 80);

        imagedestroy($sourceImage);
        imagedestroy($thumbImage);

        $data['full_image'] = $fullName;
        $data['thumbnail_image'] = $thumbName;

        return $data;
    }
}
