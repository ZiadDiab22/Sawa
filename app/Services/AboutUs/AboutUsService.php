<?php

namespace App\Services\AboutUs;

use App\Repositories\AboutUsRepository;
use Illuminate\Http\UploadedFile;

class AboutUsService
{
    public function __construct(
        protected AboutUsRepository $repository
    ) {}

    public function get(): ?array
    {
        $about = $this->repository->get();

        if (! $about) {
            return null;
        }

        return $this->format($about);
    }

    public function update(array $data): array
    {
        $about = $this->repository->firstOrCreate();

        foreach ($this->iconFields() as $field) {
            if (isset($data[$field]) && $data[$field] instanceof UploadedFile) {
                $about->$field = $data[$field]->store('about/icons', 'public');
            }
        }

        $about = $this->repository->update(
            $about,
            collect($data)->except($this->iconFields())->toArray()
        );

        return $this->format($about);
    }

    private function iconFields(): array
    {
        return [
            'facebook_icon',
            'instagram_icon',
            'website_icon',
            'whatsapp_icon',
        ];
    }

    private function format($about): array
    {
        return [
            'company_name' => $about->company_name,
            'phone' => $about->phone,
            'description' => $about->description,

            'facebook_url' => $about->facebook_url,
            'instagram_url' => $about->instagram_url,
            'website_url' => $about->website_url,
            'whatsapp_url' => $about->whatsapp_url,

            'facebook_icon' => $about->facebook_icon
                ? asset('storage/' . $about->facebook_icon)
                : null,
            'instagram_icon' => $about->instagram_icon
                ? asset('storage/' . $about->instagram_icon)
                : null,
            'website_icon' => $about->website_icon
                ? asset('storage/' . $about->website_icon)
                : null,
            'whatsapp_icon' => $about->whatsapp_icon
                ? asset('storage/' . $about->whatsapp_icon)
                : null,
        ];
    }


    public function create(array $data): array
{
    // منع إنشاء أكثر من سجل
    if ($this->repository->get()) {
        throw new \Exception('About Us already exists');
    }

    foreach ($this->iconFields() as $field) {
        if (isset($data[$field]) && $data[$field] instanceof UploadedFile) {
            $data[$field] = $data[$field]->store('about/icons', 'public');
        }
    }

    $about = $this->repository->create(
        collect($data)->except($this->iconFields())->toArray()
        + collect($data)->only($this->iconFields())->toArray()
    );

    return $this->format($about);
}

}
