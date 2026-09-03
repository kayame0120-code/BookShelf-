<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

class BookResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'author' => $this->author,
            'isbn' => $this->isbn,
            'published_date' => Carbon::parse($this->published_date)->format('Y-m-d'),
            'description' => $this->description,
            'image_url' => $this->image_url,
            'average_rating' => round((float) $this->reviews_avg_rating, 1),
            'reviews_count' => $this->reviews_count,
            'genres' => GenreResource::collection($this->whenLoaded('genres')),
            'reviews' => ReviewResource::collection($this->whenLoaded('reviews')),
        ];
    }
}
