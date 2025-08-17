# 📚 Personal Book Library App

A personal library tool built with Laravel, Inertia.js, and Vue 3 — designed to help you track, categorize, and review your books.

---

## 🚀 Features

### 👤 User Profile
- Update profile information
- View all personal notes
- View all personal reviews

---

### 🏠 Books Homepage
- View all books in your collection
- Filter by reading status (`plan_to_read`, `reading`, `read`, `on_hold`, `dropped`)
- Search and filter by title, author, category, or status
- Add new books via:
    - Google Books API
    - Barcode scanner (planned)
    - Manual entry (optional)
- Remove books from your collection
- *(Optional)* Bulk actions (e.g. change status)
- *(Optional)* Sorting (e.g. by title, rating, date added)

---

### 📘 Single Book View
- View complete book metadata
- Change your reading status
- Upload or update the book cover
- Add a personal note
- Add a personal review
- View other users’ reviews (optional)

---

### 🏷️ Categories
- Books can have multiple categories
- Categories parsed from API (e.g. `"Fiction / Action"` → `["Fiction", "Action"]`)
- Categories are deduplicated and reusable
- Filter books by category

---

### 🏢 Publishers
- Each book has one publisher (`publisher_id`)
- Publishers are deduplicated via `firstOrCreate`
- Visible on the book detail page

---

### 📊 Book Metadata
- Title
- Authors
- Page count
- Published date
- Description
- Cover image
- Publisher
- Categories

---

## 🛠️ Tech Stack

- **Backend**: Laravel 11
- **Frontend**: Vue 3 + TypeScript + Inertia.js
- **Styling**: Tailwind CSS
- **Toasts**: [`vue-sonner`](https://sonner.emilkowal.dev/)
- **File Uploads**: [Spatie MediaLibrary](https://spatie.be/docs/laravel-medialibrary)
- **API**: Google Books API

---

## 📦 Setup Instructions

```bash
git clone https://github.com/yourname/book-library.git
cd book-library

composer install
cp .env.example .env
php artisan key:generate

# Setup DB
php artisan migrate

# Install NPM dependencies and build assets
npm install && npm run dev

# (Optional) Seed with sample books
php artisan db:seed
```

---

### 📈 Roadmap / Wishlist
- Barcode scanning (mobile-friendly)
- Tagging system (e.g. “cozy”, “favourite”, “2024”)
- Public profile / shareable shelves
- Stats dashboard (pages read, authors, etc.)
- Dark mode preference
- Data export/import
