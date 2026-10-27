<?php

namespace App\Model;

use App\Entity\Album;

final readonly class AlbumSummary
{
    public function __construct(
        public Album $album,
        public int $photoCount,
    ) {
    }
}
