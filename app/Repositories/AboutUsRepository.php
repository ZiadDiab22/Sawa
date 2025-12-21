<?php

namespace App\Repositories;

use App\Models\AboutUs;

class AboutUsRepository
{
    public function get(): ?AboutUs
    {
        return AboutUs::first();
    }

    public function create(array $data): AboutUs
    {
        return AboutUs::create($data);
    }

    public function update(AboutUs $about, array $data): AboutUs
    {
        $about->update($data);
        return $about;
    }

    public function firstOrCreate(): AboutUs
    {
        return AboutUs::firstOrCreate([]);
    }
}
