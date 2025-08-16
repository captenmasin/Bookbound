<?php

namespace App\Console\Commands;

use App\Models\Book;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;
use Illuminate\Console\Command;

class GenerateSitemap extends Command
{
    protected $signature = 'app:sitemap:generate';

    public function handle(): void
    {
        $sitemap = Sitemap::create()
            ->add(
                Url::create(route('home'))
                    ->setLastModificationDate(now())
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_YEARLY)
                    ->setPriority(1)
            )
            ->add(
                Url::create(route('dashboard'))
                    ->setLastModificationDate(now())
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_YEARLY)
                    ->setPriority(0.5)
            )->add(
                Url::create(route('contact'))
                    ->setLastModificationDate(now())
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_YEARLY)
                    ->setPriority(0.3)
            )->add(
                Url::create(route('user.books.index'))
                    ->setLastModificationDate(now())
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_YEARLY)
                    ->setPriority(0.5)
            )
            ->add(
                Url::create(route('books.search'))
                    ->setLastModificationDate(now())
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_YEARLY)
                    ->setPriority(0.5)
            )->add(
                Url::create(route('login'))
                    ->setLastModificationDate(now())
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_YEARLY)
                    ->setPriority(1)
            )->add(
                Url::create(route('register'))
                    ->setLastModificationDate(now())
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_YEARLY)
                    ->setPriority(1)
            )->add(
                Url::create(route('password.request'))
                    ->setLastModificationDate(now())
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_YEARLY)
                    ->setPriority(0.5)
            )->add(
                Url::create(route('user.notes.index'))
                    ->setLastModificationDate(now())
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY)
                    ->setPriority(0.5)
            )->add(
                Url::create(route('user.reviews.index'))
                    ->setLastModificationDate(now())
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY)
                    ->setPriority(0.5)
            )->add(
                Url::create(route('user.activities.index'))
                    ->setLastModificationDate(now())
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_HOURLY)
                    ->setPriority(0.5)
            )->add(
                Url::create(route('user.settings.appearance'))
                    ->setLastModificationDate(now())
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_YEARLY)
                    ->setPriority(0.5)
            )->add(
                Url::create(route('user.settings.password.edit'))
                    ->setLastModificationDate(now())
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_YEARLY)
                    ->setPriority(0.5)
            )->add(
                Url::create(route('user.settings.password.edit'))
                    ->setLastModificationDate(now())
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_YEARLY)
                    ->setPriority(0.5)
            )->add(
                Url::create(route('user.settings.profile.danger'))
                    ->setLastModificationDate(now())
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_YEARLY)
                    ->setPriority(0.5)
            );

        $sitemap->add($this->generateBooks());

        $sitemap->writeToFile(public_path('sitemap.xml'));
    }

    private function generateBooks(): string
    {
        $path = 'sitemap_books.xml';
        $sitemap = Sitemap::create();
        foreach (Book::all() as $book) {
            $sitemap->add(Url::create(route('books.show', $book))
                ->setLastModificationDate($book->updated_at)
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                ->setPriority(0.9));
        }

        $sitemap->writeToFile(public_path($path));

        return $path;
    }
}
