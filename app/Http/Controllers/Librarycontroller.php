<?php

namespace App\Http\Controllers;

use App\Models\LibraryBook;
use App\Models\LibraryAuthor;
use App\Models\LibraryCategory;
use App\Models\LibrarySubject;
use App\Models\LibraryMember;
use App\Models\LibraryBorrowing;
use App\Models\LibraryReservation;
use App\Models\LibraryFine;
use App\Models\LibraryBookRequest;
use App\Models\LibrarySetting;
use App\Models\Student;
use App\Models\Teacher;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\Classroom;

class LibraryController extends Controller
{
    // ─────────────────────────────────────────────────────────────────────
    // DASHBOARD
    // ─────────────────────────────────────────────────────────────────────

    public function dashboard()
    {
        $schoolId = session('LoggedSchool');
        $settings = LibrarySetting::forSchool($schoolId);

        $totalBooks = LibraryBook::forSchool($schoolId)->active()->count();
        $totalCopies = LibraryBook::forSchool($schoolId)->active()->sum('total_copies');
        $availableCopies = LibraryBook::forSchool($schoolId)->active()->sum('available_copies');
        $totalMembers = LibraryMember::forSchool($schoolId)->active()->count();

        $activeBorrowings = LibraryBorrowing::where('school_id', $schoolId)
            ->whereIn('status', ['borrowed', 'overdue'])->count();

        $overdueCount = LibraryBorrowing::where('school_id', $schoolId)
            ->where('status', 'borrowed')
            ->where('due_date', '<', now())
            ->count();

        $unpaidFines = LibraryFine::where('school_id', $schoolId)
            ->where('status', 'unpaid')->sum('amount');

        $pendingRequests = LibraryBookRequest::where('school_id', $schoolId)
            ->where('status', 'pending')->count();

        $pendingReservations = LibraryReservation::where('school_id', $schoolId)
            ->whereIn('status', ['pending', 'ready'])->count();

        // Monthly borrowing trend (last 6 months)
        $monthlyTrend = LibraryBorrowing::where('school_id', $schoolId)
            ->where('borrow_date', '>=', Carbon::now()->subMonths(6)->startOfMonth())
            ->selectRaw("DATE_FORMAT(borrow_date, '%Y-%m') as month, COUNT(*) as total")
            ->groupBy('month')->orderBy('month')->get();

        // Popular books
        $popularBooks = LibraryBook::forSchool($schoolId)
            ->withCount('borrowings')
            ->orderByDesc('borrowings_count')
            ->with('author', 'category')
            ->take(5)->get();

        // Recent borrowings
        $recentBorrowings = LibraryBorrowing::where('school_id', $schoolId)
            ->with('book', 'member')
            ->orderByDesc('created_at')
            ->take(8)->get();

        // Category distribution
        $categoryStats = LibraryCategory::forSchool($schoolId)
            ->withCount('books')
            ->having('books_count', '>', 0)
            ->orderByDesc('books_count')
            ->take(6)
            ->get(['name', 'color', 'books_count as count']);

        return view('Library.dashboard', compact(
            'totalBooks',
            'totalCopies',
            'availableCopies',
            'totalMembers',
            'activeBorrowings',
            'overdueCount',
            'unpaidFines',
            'pendingRequests',
            'pendingReservations',
            'monthlyTrend',
            'popularBooks',
            'recentBorrowings',
            'categoryStats',
            'settings'
        ));
    }

    // ─────────────────────────────────────────────────────────────────────
    // BOOKS
    // ─────────────────────────────────────────────────────────────────────

    public function books(Request $request)
    {

        $schoolId = session('LoggedSchool');
        $query = LibraryBook::forSchool($schoolId)->with('author', 'category', 'subject');

        if ($request->filled('search')) {
            $query->search($request->search);
        }
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        if ($request->filled('author_id')) {
            $query->where('author_id', $request->author_id);
        }
        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }
        if ($request->filled('availability')) {
            if ($request->availability === 'available') {
                $query->where('available_copies', '>', 0);
            } else {
                $query->where('available_copies', 0);
            }
        }
        if ($request->filled('has_ebook')) {
            $query->where('has_ebook', true);
        }

        $books = $query->orderBy('title')->paginate(20)->appends($request->all());
        $categories = LibraryCategory::where('school_id', $schoolId)->active()->orderBy('name')->get();
        $authors = LibraryAuthor::where('school_id', $schoolId)->orderBy('name')->get();
        $subjects = LibrarySubject::where('school_id', $schoolId)->orderBy('name')->get();

        return view('Library.books', compact('books', 'categories', 'authors', 'subjects'));
    }

    public function createBook()
    {
        $schoolId = session('LoggedSchool');
        $categories = LibraryCategory::where('school_id', $schoolId)->active()->orderBy('name')->get();
        $authors = LibraryAuthor::where('school_id', $schoolId)->orderBy('name')->get();
        $subjects = LibrarySubject::where('school_id', $schoolId)->orderBy('name')->get();
        return view('Library.book-form', compact('categories', 'authors', 'subjects'));
    }

    public function storeBook(Request $request)
    {
        $schoolId = session('LoggedSchool');
        $userId = session('LoggedUser');

        try {
            $request->validate([
                'title' => 'required|string|max:255',
                'isbn' => 'nullable|string|max:30',
                'total_copies' => 'required|integer|min:1',
                'available_copies' => 'required|integer|min:0',
                'cover_image' => 'nullable|image|max:2048',
                'ebook_file' => 'nullable|file|mimes:pdf,epub|max:51200',
            ]);

            $data = $request->except(['cover_image', 'ebook_file', '_token']);
            $data['school_id'] = $schoolId;
            $data['added_by'] = $userId;

            if ($request->hasFile('cover_image')) {
                $data['cover_image'] = $request->file('cover_image')
                    ->store("library/{$schoolId}/covers", 'public');
            }
            if ($request->hasFile('ebook_file')) {
                $data['ebook_file'] = $request->file('ebook_file')
                    ->store("library/{$schoolId}/ebooks", 'public');
                $data['has_ebook'] = true;
            }

            $book = LibraryBook::create($data);

            // Check if it's an AJAX request
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Book added successfully!',
                    'book' => $book
                ]);
            }

            return redirect()->route('library.books')
                ->with('success', 'Book added successfully!');

        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors()
                ], 422);
            }
            throw $e;

        } catch (\Exception $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to add book: ' . $e->getMessage()
                ], 500);
            }

            return back()->with('error', 'Failed to add book: ' . $e->getMessage());
        }
    }

    public function editBook(int $id)
    {
        $schoolId = session('LoggedSchool');

        try {
            $book = LibraryBook::where('school_id', $schoolId)->findOrFail($id);
            $categories = LibraryCategory::where('school_id', $schoolId)->active()->orderBy('name')->get();
            $authors = LibraryAuthor::where('school_id', $schoolId)->orderBy('name')->get();
            $subjects = LibrarySubject::where('school_id', $schoolId)->orderBy('name')->get();

            // Check if it's an AJAX request
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'book' => $book,
                    'categories' => $categories,
                    'authors' => $authors,
                    'subjects' => $subjects
                ]);
            }

            return view('Library.book-form', compact('book', 'categories', 'authors', 'subjects'));

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Book not found.'
                ], 404);
            }

            return redirect()->route('library.books')->with('error', 'Book not found.');

        } catch (\Exception $e) {
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to load book: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->route('library.books')->with('error', 'Failed to load book.');
        }
    }

    public function updateBook(Request $request, int $id)
    {
        $schoolId = session('LoggedSchool');

        try {
            $book = LibraryBook::where('school_id', $schoolId)->findOrFail($id);

            $request->validate([
                'title' => 'required|string|max:255',
                'total_copies' => 'required|integer|min:1',
                'available_copies' => 'nullable|integer|min:0',
                'cover_image' => 'nullable|image|max:2048',
                'ebook_file' => 'nullable|file|mimes:pdf,epub|max:51200',
            ]);

            $data = $request->except(['cover_image', 'ebook_file', '_token', '_method']);

            // Set available_copies if not provided
            if (!isset($data['available_copies']) || $data['available_copies'] === null) {
                $data['available_copies'] = $data['total_copies'];
            }

            if ($request->hasFile('cover_image')) {
                if ($book->cover_image)
                    Storage::disk('public')->delete($book->cover_image);
                $data['cover_image'] = $request->file('cover_image')
                    ->store("library/{$schoolId}/covers", 'public');
            }

            if ($request->hasFile('ebook_file')) {
                if ($book->ebook_file)
                    Storage::disk('public')->delete($book->ebook_file);
                $data['ebook_file'] = $request->file('ebook_file')
                    ->store("library/{$schoolId}/ebooks", 'public');
                $data['has_ebook'] = true;
            }

            $book->update($data);

            // Check if it's an AJAX request
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Book updated successfully!',
                    'book' => $book
                ]);
            }

            return redirect()->route('library.books')
                ->with('success', 'Book updated successfully!');

        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors()
                ], 422);
            }
            throw $e;

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Book not found.'
                ], 404);
            }

            return back()->with('error', 'Book not found.');

        } catch (\Exception $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update book: ' . $e->getMessage()
                ], 500);
            }

            return back()->with('error', 'Failed to update book: ' . $e->getMessage());
        }
    }

    public function deleteBook(int $id)
    {
        $schoolId = session('LoggedSchool');

        try {
            $book = LibraryBook::where('school_id', $schoolId)->findOrFail($id);

            if ($book->activeBorrowings()->count() > 0) {
                $message = 'Cannot delete a book that is currently borrowed.';

                if (request()->ajax() || request()->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => $message
                    ], 400);
                }

                return back()->with('error', $message);
            }

            // Delete associated files
            if ($book->cover_image)
                Storage::disk('public')->delete($book->cover_image);
            if ($book->ebook_file)
                Storage::disk('public')->delete($book->ebook_file);

            $book->delete();

            // Check if it's an AJAX request
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Book deleted successfully.'
                ]);
            }

            return back()->with('success', 'Book deleted successfully.');

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Book not found.'
                ], 404);
            }

            return back()->with('error', 'Book not found.');

        } catch (\Exception $e) {
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete book: ' . $e->getMessage()
                ], 500);
            }

            return back()->with('error', 'Failed to delete book: ' . $e->getMessage());
        }
    }

    public function showBook(int $id)
    {
        $schoolId = session('LoggedSchool');
        $book = LibraryBook::where('school_id', $schoolId)
            ->with('author', 'category', 'subject')
            ->findOrFail($id);
        $borrowings = LibraryBorrowing::where('book_id', $id)
            ->with('member')
            ->orderByDesc('borrow_date')
            ->take(10)->get();
        $reservations = LibraryReservation::where('book_id', $id)
            ->with('member')
            ->whereIn('status', ['pending', 'ready'])
            ->get();

        // Recommendations – same category/author
        $recommendations = LibraryBook::forSchool($schoolId)
            ->where('id', '!=', $id)
            ->where(function ($q) use ($book) {
                $q->where('category_id', $book->category_id)
                    ->orWhere('author_id', $book->author_id);
            })
            ->with('author', 'category')
            ->take(4)->get();

        return view('Library.book-detail', compact('book', 'borrowings', 'reservations', 'recommendations'));
    }

    public function importBooks(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:xlsx,csv']);
        $schoolId = session('LoggedSchool');

        try {
            $rows = \Maatwebsite\Excel\Facades\Excel::toArray([], $request->file('file'));
            $imported = 0;
            foreach ($rows[0] as $i => $row) {
                if ($i === 0)
                    continue; // skip header
                if (empty($row[0]))
                    continue;

                $author = null;
                if (!empty($row[2])) {
                    $author = LibraryAuthor::firstOrCreate(
                        ['school_id' => $schoolId, 'name' => trim($row[2])],
                        ['is_active' => true]
                    );
                }
                $category = null;
                if (!empty($row[3])) {
                    $category = LibraryCategory::firstOrCreate(
                        ['school_id' => $schoolId, 'name' => trim($row[3])],
                        ['slug' => Str::slug($row[3]), 'is_active' => true]
                    );
                }

                LibraryBook::create([
                    'school_id' => $schoolId,
                    'title' => trim($row[0]),
                    'isbn' => $row[1] ?? null,
                    'author_id' => $author?->id,
                    'category_id' => $category?->id,
                    'publisher' => $row[4] ?? null,
                    'publication_year' => $row[5] ?? null,
                    'total_copies' => (int) ($row[6] ?? 1),
                    'available_copies' => (int) ($row[6] ?? 1),
                    'language' => $row[7] ?? 'English',
                    'is_active' => true,
                    'added_by' => session('LoggedUser'),
                ]);
                $imported++;
            }
            return back()->with('success', "{$imported} books imported successfully.");
        } catch (\Exception $e) {
            return back()->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    public function exportBooks()
    {
        $schoolId = session('LoggedSchool');
        $books = LibraryBook::forSchool($schoolId)->with('author', 'category', 'subject')->get();

        $filename = "library_books_" . date('Y_m_d') . ".csv";
        $headers = ['Content-Type' => 'text/csv', 'Content-Disposition' => "attachment; filename={$filename}"];

        $callback = function () use ($books) {
            $f = fopen('php://output', 'w');
            fputcsv($f, ['Title', 'ISBN', 'Author', 'Category', 'Subject', 'Publisher', 'Year', 'Total Copies', 'Available', 'Language', 'Price', 'Location']);
            foreach ($books as $b) {
                fputcsv($f, [
                    $b->title,
                    $b->isbn,
                    $b->author?->name,
                    $b->category?->name,
                    $b->subject?->name,
                    $b->publisher,
                    $b->publication_year,
                    $b->total_copies,
                    $b->available_copies,
                    $b->language,
                    $b->price,
                    $b->location,
                ]);
            }
            fclose($f);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function downloadEbook(int $id)
    {
        $schoolId = session('LoggedSchool');
        $book = LibraryBook::where('school_id', $schoolId)->findOrFail($id);

        if (!$book->has_ebook || !$book->ebook_file) {
            return back()->with('error', 'No e-book available for this title.');
        }

        $member = $this->getSessionMember($schoolId);
        if (!$member) {
            return back()->with('error', 'You must be a library member to access e-books.');
        }

        return Storage::disk('public')->download($book->ebook_file, $book->title . '.pdf');
    }

    // ─────────────────────────────────────────────────────────────────────
    // CATEGORIES
    // ─────────────────────────────────────────────────────────────────────

    public function categories()
    {
        $schoolId = session('LoggedSchool');
        $categories = LibraryCategory::where('school_id', $schoolId)
            ->withCount('books')->orderBy('name')->paginate(20);
        return view('Library.categories', compact('categories'));
    }

    public function storeCategory(Request $request)
    {
        $schoolId = session('LoggedSchool');

        try {
            $request->validate([
                'name' => 'required|string|max:100',
                'color' => 'nullable|string|max:20'
            ]);

            $category = LibraryCategory::create([
                'school_id' => $schoolId,
                'name' => $request->name,
                'slug' => Str::slug($request->name),
                'description' => $request->description,
                'color' => $request->color ?? '#5351e4',
                'is_active' => true,
            ]);

            // Check if it's an AJAX request
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Category created successfully.',
                    'category' => $category
                ]);
            }

            return back()->with('success', 'Category created.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors()
                ], 422);
            }
            throw $e;

        } catch (\Exception $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create category: ' . $e->getMessage()
                ], 500);
            }

            return back()->with('error', 'Failed to create category.');
        }
    }

    public function updateCategory(Request $request, int $id)
    {
        $schoolId = session('LoggedSchool');

        try {
            $cat = LibraryCategory::where('school_id', $schoolId)->findOrFail($id);

            $request->validate([
                'name' => 'required|string|max:100'
            ]);

            $cat->update([
                'name' => $request->name,
                'slug' => Str::slug($request->name),
                'description' => $request->description,
                'color' => $request->color ?? $cat->color,
                'is_active' => $request->boolean('is_active', true),
            ]);

            // Check if it's an AJAX request
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Category updated successfully.',
                    'category' => $cat
                ]);
            }

            return back()->with('success', 'Category updated.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors()
                ], 422);
            }
            throw $e;

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Category not found.'
                ], 404);
            }

            return back()->with('error', 'Category not found.');

        } catch (\Exception $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update category: ' . $e->getMessage()
                ], 500);
            }

            return back()->with('error', 'Failed to update category.');
        }
    }

    public function deleteCategory(int $id)
    {
        $schoolId = session('LoggedSchool');

        try {
            $cat = LibraryCategory::where('school_id', $schoolId)->findOrFail($id);

            if ($cat->books()->count() > 0) {
                $message = 'Cannot delete a category that has books assigned to it.';

                if (request()->ajax() || request()->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => $message
                    ], 400);
                }

                return back()->with('error', $message);
            }

            $cat->delete();

            // Check if it's an AJAX request
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Category deleted successfully.'
                ]);
            }

            return back()->with('success', 'Category deleted.');

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Category not found.'
                ], 404);
            }

            return back()->with('error', 'Category not found.');

        } catch (\Exception $e) {
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete category: ' . $e->getMessage()
                ], 500);
            }

            return back()->with('error', 'Failed to delete category.');
        }
    }

    // ─────────────────────────────────────────────────────────────────────
    // AUTHORS
    // ─────────────────────────────────────────────────────────────────────

    public function authors()
    {
        $schoolId = session('LoggedSchool');
        $authors = LibraryAuthor::where('school_id', $schoolId)
            ->withCount('books')->orderBy('name')->paginate(20);
        return view('Library.authors', compact('authors'));
    }

    public function storeAuthor(Request $request)
    {
        $schoolId = session('LoggedSchool');
        $request->validate(['name' => 'required|string|max:150']);
        LibraryAuthor::create([
            'school_id' => $schoolId,
            'name' => $request->name,
            'bio' => $request->bio,
            'nationality' => $request->nationality,
            'is_active' => true,
        ]);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Author added successfully']);
        }

        return redirect()->route('library.authors.index')->with('success', 'Author added successfully');
    }

    public function updateAuthor(Request $request, int $id)
    {
        $schoolId = session('LoggedSchool');
        $author = LibraryAuthor::where('school_id', $schoolId)->findOrFail($id);
        $request->validate(['name' => 'required|string|max:150']);
        $author->update($request->only(['name', 'bio', 'nationality', 'is_active']));

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Author updated successfully']);
        }

        return redirect()->route('library.authors.index')->with('success', 'Author updated successfully');
    }

    public function deleteAuthor(int $id)
    {
        $schoolId = session('LoggedSchool');
        $author = LibraryAuthor::where('school_id', $schoolId)->findOrFail($id);
        if ($author->books()->count() > 0) {
            return back()->with('error', 'Cannot delete an author with books assigned.');
        }
        $author->delete();

        if (request()->ajax()) {
            return response()->json(['success' => true, 'message' => 'Author deleted successfully']);
        }

        return redirect()->route('library.authors.index')->with('success', 'Author deleted successfully');
    }

    // ─────────────────────────────────────────────────────────────────────
    // SUBJECTS
    // ─────────────────────────────────────────────────────────────────────

    public function subjects()
    {
        $schoolId = session('LoggedSchool');
        $subjects = LibrarySubject::where('school_id', $schoolId)
            ->withCount('books')->orderBy('name')->paginate(20);
        return view('Library.subjects', compact('subjects'));
    }

    public function storeSubject(Request $request)
    {
        $schoolId = session('LoggedSchool');

        try {
            $request->validate(['name' => 'required|string|max:100']);

            $subject = LibrarySubject::create([
                'school_id' => $schoolId,
                'name' => $request->name,
                'description' => $request->description,
                'is_active' => true,
            ]);

            // Check if it's an AJAX request
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Subject added successfully.',
                    'subject' => $subject
                ]);
            }

            return back()->with('success', 'Subject added.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors()
                ], 422);
            }
            throw $e;

        } catch (\Exception $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to add subject: ' . $e->getMessage()
                ], 500);
            }

            return back()->with('error', 'Failed to add subject.');
        }
    }

    public function updateSubject(Request $request, int $id)
    {
        $schoolId = session('LoggedSchool');

        try {
            $subject = LibrarySubject::where('school_id', $schoolId)->findOrFail($id);

            $request->validate(['name' => 'required|string|max:100']);

            $subject->update($request->only(['name', 'description', 'is_active']));

            // Check if it's an AJAX request
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Subject updated successfully.',
                    'subject' => $subject
                ]);
            }

            return back()->with('success', 'Subject updated.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors()
                ], 422);
            }
            throw $e;

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Subject not found.'
                ], 404);
            }

            return back()->with('error', 'Subject not found.');

        } catch (\Exception $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update subject: ' . $e->getMessage()
                ], 500);
            }

            return back()->with('error', 'Failed to update subject.');
        }
    }

    public function deleteSubject(int $id)
    {
        $schoolId = session('LoggedSchool');

        try {
            $subject = LibrarySubject::where('school_id', $schoolId)->findOrFail($id);

            if ($subject->books()->count() > 0) {
                $message = 'Cannot delete a subject with books assigned.';

                if (request()->ajax() || request()->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => $message
                    ], 400);
                }

                return back()->with('error', $message);
            }

            $subject->delete();

            // Check if it's an AJAX request
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Subject deleted successfully.'
                ]);
            }

            return back()->with('success', 'Subject deleted.');

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Subject not found.'
                ], 404);
            }

            return back()->with('error', 'Subject not found.');

        } catch (\Exception $e) {
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete subject: ' . $e->getMessage()
                ], 500);
            }

            return back()->with('error', 'Failed to delete subject.');
        }
    }

    // ─────────────────────────────────────────────────────────────────────
    // MEMBERS
    // ─────────────────────────────────────────────────────────────────────

    public function members(Request $request)
    {
        $schoolId = session('LoggedSchool');
        $query = LibraryMember::forSchool($schoolId);

        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function ($q) use ($term) {
                $q->where('library_card_number', 'like', "%{$term}%");
                // Search by student/teacher name
                $studentIds = Student::where('school_id', session('LoggedSchool'))
                    ->where(function ($sq) use ($term) {
                        $sq->where('firstname', 'like', "%{$term}%")
                            ->orWhere('lastname', 'like', "%{$term}%");
                    })->pluck('id');
                $teacherIds = Teacher::where('school_id', session('LoggedSchool'))
                    ->where(function ($tq) use ($term) {
                        $tq->where('firstname', 'like', "%{$term}%")
                            ->orWhere('surname', 'like', "%{$term}%");
                    })->pluck('id');
                $q->orWhere(function ($q2) use ($studentIds) {
                    $q2->where('member_type', 'student')->whereIn('member_id', $studentIds);
                })->orWhere(function ($q2) use ($teacherIds) {
                    $q2->where('member_type', 'teacher')->whereIn('member_id', $teacherIds);
                });
            });
        }
        if ($request->filled('status'))
            $query->where('status', $request->status);
        if ($request->filled('type'))
            $query->where('member_type', $request->type);

        $members = $query->orderByDesc('created_at')->paginate(20)->appends($request->all());
        $teachers = Teacher::where('school_id', $schoolId)->orderBy('firstname')->get();

        $classrooms = Classroom::where('school_id', $schoolId)
            ->orderBy('class_name')
            ->get();

        return view('Library.members', compact('members', 'teachers', 'classrooms'));
    }

    public function storeMember(Request $request)
    {
        $schoolId = session('LoggedSchool');
        $userId = session('LoggedUser');

        try {
            $request->validate([
                'member_type' => 'required|in:student,teacher',
                'membership_date' => 'required|date',
                'expiry_date' => 'nullable|date',
            ]);

            $settings = LibrarySetting::forSchool($schoolId);
            $memberType = $request->member_type;

            // ── BULK: multiple students via member_ids[] ──────────────────
            if ($memberType === 'student' && $request->has('member_ids')) {
                $ids = array_filter((array) $request->member_ids);

                if (empty($ids)) {
                    $msg = 'No students selected.';
                    if ($request->ajax() || $request->wantsJson()) {
                        return response()->json(['success' => false, 'message' => $msg], 400);
                    }
                    return back()->with('error', $msg);
                }

                $registered = 0;
                $skipped = 0;

                foreach ($ids as $studentId) {
                    $exists = LibraryMember::where('school_id', $schoolId)
                        ->where('member_type', 'student')
                        ->where('member_id', $studentId)
                        ->exists();

                    if ($exists) {
                        $skipped++;
                        continue;
                    }

                    LibraryMember::create([
                        'school_id' => $schoolId,
                        'member_type' => 'student',
                        'member_id' => $studentId,
                        'library_card_number' => 'LIB-' . strtoupper(Str::random(8)),
                        'membership_date' => $request->membership_date,
                        'expiry_date' => $request->expiry_date ?? null,
                        'max_books_allowed' => $settings->student_max_books,
                        'max_days_allowed' => $settings->student_loan_days,
                        'status' => 'active',
                        'added_by' => $userId,
                    ]);

                    $registered++;
                }

                $msg = "{$registered} student(s) registered successfully.";
                if ($skipped) {
                    $msg .= " {$skipped} skipped (already members).";
                }

                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json(['success' => true, 'message' => $msg]);
                }
                return back()->with('success', $msg);
            }

            // ── SINGLE: one teacher or one student via member_id ──────────
            $request->validate(['member_id' => 'required|integer']);

            $exists = LibraryMember::where('school_id', $schoolId)
                ->where('member_type', $memberType)
                ->where('member_id', $request->member_id)
                ->exists();

            if ($exists) {
                $msg = 'This person is already a library member.';
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json(['success' => false, 'message' => $msg], 400);
                }
                return back()->with('error', $msg);
            }

            $member = LibraryMember::create([
                'school_id' => $schoolId,
                'member_type' => $memberType,
                'member_id' => $request->member_id,
                'library_card_number' => 'LIB-' . strtoupper(Str::random(8)),
                'membership_date' => $request->membership_date,
                'expiry_date' => $request->expiry_date ?? null,
                'max_books_allowed' => $memberType === 'teacher'
                    ? $settings->teacher_max_books
                    : $settings->student_max_books,
                'max_days_allowed' => $memberType === 'teacher'
                    ? $settings->teacher_loan_days
                    : $settings->student_loan_days,
                'status' => 'active',
                'added_by' => $userId,
            ]);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Library member registered successfully.',
                    'member' => $member,
                ]);
            }
            return back()->with('success', 'Library member registered successfully.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors(),
                ], 422);
            }
            throw $e;

        } catch (\Exception $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to register member: ' . $e->getMessage(),
                ], 500);
            }
            return back()->with('error', 'Failed to register member: ' . $e->getMessage());
        }
    }

    public function updateMember(Request $request, int $id)
    {
        $schoolId = session('LoggedSchool');

        try {
            $member = LibraryMember::where('school_id', $schoolId)->findOrFail($id);

            $request->validate([
                'max_books_allowed' => 'required|integer|min:1',
                'max_days_allowed' => 'required|integer|min:1',
                'status' => 'required|in:active,suspended,expired',
            ]);

            $member->update([
                'max_books_allowed' => $request->max_books_allowed,
                'max_days_allowed' => $request->max_days_allowed,
                'expiry_date' => $request->expiry_date ?? $member->expiry_date,
                'status' => $request->status,
                'suspension_reason' => $request->suspension_reason,
            ]);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Member updated successfully.',
                    'member' => $member
                ]);
            }

            return back()->with('success', 'Member updated.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors()
                ], 422);
            }
            throw $e;

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Member not found.'
                ], 404);
            }
            return back()->with('error', 'Member not found.');

        } catch (\Exception $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update member: ' . $e->getMessage()
                ], 500);
            }
            return back()->with('error', 'Failed to update member.');
        }
    }

    public function deleteMember(int $id)
    {
        $schoolId = session('LoggedSchool');

        try {
            $member = LibraryMember::where('school_id', $schoolId)->findOrFail($id);

            if ($member->activeBorrowings()->count() > 0) {
                $message = 'Cannot remove a member with active borrowings.';
                if (request()->ajax() || request()->wantsJson()) {
                    return response()->json(['success' => false, 'message' => $message], 400);
                }
                return back()->with('error', $message);
            }

            $member->delete();

            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Member removed successfully.'
                ]);
            }

            return back()->with('success', 'Member removed.');

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Member not found.'
                ], 404);
            }
            return back()->with('error', 'Member not found.');

        } catch (\Exception $e) {
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete member: ' . $e->getMessage()
                ], 500);
            }
            return back()->with('error', 'Failed to delete member.');
        }
    }
    // ─────────────────────────────────────────────────────────────────────
    // BORROWINGS
    // ─────────────────────────────────────────────────────────────────────

    public function borrowings(Request $request)
    {
        $schoolId = session('LoggedSchool');
        $query = LibraryBorrowing::where('school_id', $schoolId)
            ->with('book', 'member');

        if ($request->filled('status'))
            $query->where('status', $request->status);
        if ($request->filled('book_id'))
            $query->where('book_id', $request->book_id);
        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function ($q) use ($term) {
                $q->where('borrow_number', 'like', "%{$term}%")
                    ->orWhereHas('book', fn($b) => $b->where('title', 'like', "%{$term}%"))
                    ->orWhereHas('member', fn($m) => $m->where('library_card_number', 'like', "%{$term}%"));
            });
        }

        // Auto-update overdue
        LibraryBorrowing::where('school_id', $schoolId)
            ->where('status', 'borrowed')
            ->where('due_date', '<', now())
            ->update(['status' => 'overdue']);

        $borrowings = $query->orderByDesc('borrow_date')->paginate(20)->appends($request->all());
        $books = LibraryBook::forSchool($schoolId)->active()->orderBy('title')->get();
        $members = LibraryMember::forSchool($schoolId)->active()->get();
        $settings = LibrarySetting::forSchool($schoolId);

        return view('Library.borrowings', compact('borrowings', 'books', 'members', 'settings'));
    }

    public function borrowBook(Request $request)
    {
        $schoolId = session('LoggedSchool');
        $userId = session('LoggedUser');

        try {
            $request->validate([
                'book_id' => 'required|integer',
                'member_id' => 'required|integer',
                'due_date' => 'required|date|after:today',
            ]);

            DB::beginTransaction();

            $book = LibraryBook::where('school_id', $schoolId)->findOrFail($request->book_id);
            $member = LibraryMember::where('school_id', $schoolId)->findOrFail($request->member_id);
            $settings = LibrarySetting::forSchool($schoolId);

            if ($book->available_copies < 1) {
                $message = 'No copies available for this book.';
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json(['success' => false, 'message' => $message], 400);
                }
                return back()->with('error', $message);
            }

            if (!$member->canBorrow()) {
                $message = 'This member cannot borrow books (check status, limit, or unpaid fines).';
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json(['success' => false, 'message' => $message], 400);
                }
                return back()->with('error', $message);
            }

            $borrowing = LibraryBorrowing::create([
                'school_id' => $schoolId,
                'book_id' => $book->id,
                'member_id' => $member->id,
                'borrow_number' => 'BRW-' . strtoupper(Str::random(8)),
                'borrow_date' => now()->toDateString(),
                'due_date' => $request->due_date,
                'status' => 'borrowed',
                'notes' => $request->notes,
                'issued_by' => $userId,
            ]);

            $book->decrement('available_copies');

            DB::commit();

            $message = "Book issued to {$member->name}. Due: " . \Carbon\Carbon::parse($request->due_date)->format('d M Y');

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'borrowing' => $borrowing
                ]);
            }

            return back()->with('success', $message);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors()
                ], 422);
            }
            throw $e;

        } catch (\Exception $e) {
            DB::rollBack();
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to issue book: ' . $e->getMessage()
                ], 500);
            }
            return back()->with('error', 'Failed to issue book: ' . $e->getMessage());
        }
    }

    public function returnBook(Request $request, int $borrowingId)
    {
        $schoolId = session('LoggedSchool');
        $userId = session('LoggedUser');

        try {
            $borrowing = LibraryBorrowing::where('school_id', $schoolId)->findOrFail($borrowingId);

            if (!in_array($borrowing->status, ['borrowed', 'overdue'])) {
                $message = 'This borrowing is not active.';
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json(['success' => false, 'message' => $message], 400);
                }
                return back()->with('error', $message);
            }

            DB::beginTransaction();

            $settings = LibrarySetting::forSchool($schoolId);
            $returnDate = now();
            $overdueDays = 0;
            $fineAmount = 0;

            if ($returnDate->gt($borrowing->due_date)) {
                $overdueDays = (int) $returnDate->diffInDays($borrowing->due_date);
                $fineAmount = $overdueDays * $settings->fine_per_day;
            }

            $borrowing->update([
                'return_date' => $returnDate->toDateString(),
                'status' => 'returned',
                'returned_to' => $userId,
            ]);

            $borrowing->book->increment('available_copies');

            if ($fineAmount > 0) {
                LibraryFine::create([
                    'school_id' => $schoolId,
                    'borrowing_id' => $borrowing->id,
                    'member_id' => $borrowing->member_id,
                    'amount' => $fineAmount,
                    'overdue_days' => $overdueDays,
                    'status' => 'unpaid',
                    'processed_by' => $userId,
                ]);
            }

            DB::commit();

            $msg = "Book returned successfully.";
            if ($fineAmount > 0) {
                $msg .= " Fine generated: UGX " . number_format($fineAmount, 0);
            }

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $msg,
                    'fine_amount' => $fineAmount
                ]);
            }

            return back()->with('success', $msg);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Borrowing record not found.'
                ], 404);
            }
            return back()->with('error', 'Borrowing record not found.');

        } catch (\Exception $e) {
            DB::rollBack();
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Return failed: ' . $e->getMessage()
                ], 500);
            }
            return back()->with('error', 'Return failed: ' . $e->getMessage());
        }
    }

    public function renewBorrowing(Request $request, int $borrowingId)
    {
        $schoolId = session('LoggedSchool');

        try {
            $borrowing = LibraryBorrowing::where('school_id', $schoolId)->findOrFail($borrowingId);
            $settings = LibrarySetting::forSchool($schoolId);

            if ($borrowing->renewals >= $settings->max_renewals) {
                $message = 'Maximum renewals reached for this borrowing.';
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json(['success' => false, 'message' => $message], 400);
                }
                return back()->with('error', $message);
            }

            if ($borrowing->status !== 'borrowed') {
                $message = 'Only active borrowings can be renewed.';
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json(['success' => false, 'message' => $message], 400);
                }
                return back()->with('error', $message);
            }

            $newDueDate = \Carbon\Carbon::parse($borrowing->due_date)->addDays($borrowing->member->max_days_allowed);

            $borrowing->update([
                'due_date' => $newDueDate->toDateString(),
                'renewals' => $borrowing->renewals + 1,
            ]);

            $message = "Renewed. New due date: {$newDueDate->format('d M Y')}";

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'new_due_date' => $newDueDate->toDateString()
                ]);
            }

            return back()->with('success', $message);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Borrowing record not found.'
                ], 404);
            }
            return back()->with('error', 'Borrowing record not found.');

        } catch (\Exception $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Renewal failed: ' . $e->getMessage()
                ], 500);
            }
            return back()->with('error', 'Renewal failed: ' . $e->getMessage());
        }
    }

    public function markLost(Request $request, int $borrowingId)
    {
        $schoolId = session('LoggedSchool');

        try {
            $borrowing = LibraryBorrowing::where('school_id', $schoolId)->findOrFail($borrowingId);

            $borrowing->update(['status' => 'lost']);

            $message = 'Borrowing marked as lost.';

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message
                ]);
            }

            return back()->with('success', $message);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Borrowing record not found.'
                ], 404);
            }
            return back()->with('error', 'Borrowing record not found.');

        } catch (\Exception $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to mark as lost: ' . $e->getMessage()
                ], 500);
            }
            return back()->with('error', 'Failed to mark as lost: ' . $e->getMessage());
        }
    }

    // ─────────────────────────────────────────────────────────────────────
    // RESERVATIONS
    // ─────────────────────────────────────────────────────────────────────

    public function reservations(Request $request)
    {
        $schoolId = session('LoggedSchool');
        $query = LibraryReservation::where('school_id', $schoolId)->with('book', 'member');
        if ($request->filled('status'))
            $query->where('status', $request->status);

        $reservations = $query->orderByDesc('reservation_date')->paginate(20)->appends($request->all());
        $books = LibraryBook::forSchool($schoolId)->active()->orderBy('title')->get();
        $members = LibraryMember::forSchool($schoolId)->active()->get();

        return view('Library.reservations', compact('reservations', 'books', 'members'));
    }

    public function storeReservation(Request $request)
    {
        $schoolId = session('LoggedSchool');

        try {
            $request->validate([
                'book_id' => 'required|integer',
                'member_id' => 'required|integer',
            ]);

            $existing = LibraryReservation::where('school_id', $schoolId)
                ->where('book_id', $request->book_id)
                ->where('member_id', $request->member_id)
                ->whereIn('status', ['pending', 'ready'])
                ->exists();

            if ($existing) {
                $message = 'This member already has an active reservation for this book.';
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json(['success' => false, 'message' => $message], 400);
                }
                return back()->with('error', $message);
            }

            $reservation = LibraryReservation::create([
                'school_id' => $schoolId,
                'book_id' => $request->book_id,
                'member_id' => $request->member_id,
                'reservation_number' => 'RES-' . strtoupper(Str::random(8)),
                'reservation_date' => now()->toDateString(),
                'expiry_date' => Carbon::now()->addDays(7)->toDateString(),
                'status' => 'pending',
                'notes' => $request->notes,
            ]);

            $message = 'Reservation created successfully.';

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'reservation' => $reservation
                ]);
            }

            return back()->with('success', $message);

        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors()
                ], 422);
            }
            throw $e;

        } catch (\Exception $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create reservation: ' . $e->getMessage()
                ], 500);
            }
            return back()->with('error', 'Failed to create reservation: ' . $e->getMessage());
        }
    }

    public function updateReservationStatus(Request $request, int $id)
    {
        $schoolId = session('LoggedSchool');

        try {
            $reservation = LibraryReservation::where('school_id', $schoolId)->findOrFail($id);

            $request->validate([
                'status' => 'required|in:pending,ready,fulfilled,cancelled,expired'
            ]);

            $oldStatus = $reservation->status;
            $reservation->update(['status' => $request->status]);

            $statusMessages = [
                'ready' => 'Reservation marked as ready for pickup.',
                'fulfilled' => 'Reservation marked as fulfilled.',
                'cancelled' => 'Reservation cancelled.',
                'expired' => 'Reservation expired.',
                'pending' => 'Reservation status updated to pending.'
            ];

            $message = $statusMessages[$request->status] ?? 'Reservation status updated successfully.';

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'old_status' => $oldStatus,
                    'new_status' => $request->status
                ]);
            }

            return back()->with('success', $message);

        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors()
                ], 422);
            }
            throw $e;

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Reservation not found.'
                ], 404);
            }
            return back()->with('error', 'Reservation not found.');

        } catch (\Exception $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update status: ' . $e->getMessage()
                ], 500);
            }
            return back()->with('error', 'Failed to update status: ' . $e->getMessage());
        }
    }
    // ─────────────────────────────────────────────────────────────────────
    // FINES
    // ─────────────────────────────────────────────────────────────────────

    public function fines(Request $request)
    {
        $schoolId = session('LoggedSchool');
        $query = LibraryFine::where('school_id', $schoolId)
            ->with('member', 'borrowing.book');

        if ($request->filled('status'))
            $query->where('status', $request->status);
        if ($request->filled('member_id'))
            $query->where('member_id', $request->member_id);

        $fines = $query->orderByDesc('created_at')->paginate(20)->appends($request->all());
        $totalUnpaid = LibraryFine::where('school_id', $schoolId)->where('status', 'unpaid')->sum('amount');
        $totalPaid = LibraryFine::where('school_id', $schoolId)->where('status', 'paid')->sum('amount');
        $totalWaived = LibraryFine::where('school_id', $schoolId)->where('status', 'waived')->sum('amount');

        return view('Library.fines', compact('fines', 'totalUnpaid', 'totalPaid', 'totalWaived'));
    }

    public function payFine(Request $request, int $id)
    {
        $schoolId = session('LoggedSchool');
        $userId = session('LoggedUser');

        try {
            $fine = LibraryFine::where('school_id', $schoolId)->findOrFail($id);

            if ($fine->status !== 'unpaid') {
                $message = 'Fine is not unpaid.';
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json(['success' => false, 'message' => $message], 400);
                }
                return back()->with('error', $message);
            }

            $fine->update([
                'status' => 'paid',
                'paid_date' => now()->toDateString(),
                'processed_by' => $userId,
            ]);

            $message = 'Fine marked as paid successfully.';

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'fine' => $fine
                ]);
            }

            return back()->with('success', $message);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Fine record not found.'
                ], 404);
            }
            return back()->with('error', 'Fine record not found.');

        } catch (\Exception $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to process payment: ' . $e->getMessage()
                ], 500);
            }
            return back()->with('error', 'Failed to process payment: ' . $e->getMessage());
        }
    }

    public function waiveFine(Request $request, int $id)
    {
        $schoolId = session('LoggedSchool');
        $userId = session('LoggedUser');

        try {
            $fine = LibraryFine::where('school_id', $schoolId)->findOrFail($id);

            $request->validate([
                'waive_reason' => 'required|string|max:255'
            ]);

            $fine->update([
                'status' => 'waived',
                'waive_reason' => $request->waive_reason,
                'processed_by' => $userId,
            ]);

            $message = 'Fine waived successfully.';

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'fine' => $fine
                ]);
            }

            return back()->with('success', $message);

        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors()
                ], 422);
            }
            throw $e;

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Fine record not found.'
                ], 404);
            }
            return back()->with('error', 'Fine record not found.');

        } catch (\Exception $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to waive fine: ' . $e->getMessage()
                ], 500);
            }
            return back()->with('error', 'Failed to waive fine: ' . $e->getMessage());
        }
    }

    // ─────────────────────────────────────────────────────────────────────
    // BOOK REQUESTS
    // ─────────────────────────────────────────────────────────────────────

    public function bookRequests(Request $request)
    {
        $schoolId = session('LoggedSchool');
        $query = LibraryBookRequest::where('school_id', $schoolId)->with('member');
        if ($request->filled('status'))
            $query->where('status', $request->status);

        $requests = $query->orderByDesc('created_at')->paginate(20)->appends($request->all());
        $member = $this->getSessionMember($schoolId);

        return view('Library.book-requests', compact('requests', 'member'));
    }
    public function storeBookRequest(Request $request)
    {
        $schoolId = session('LoggedSchool');
        $member = $this->getSessionMember($schoolId);

        if (!$member) {
            $message = 'You must be a library member to request books.';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $message], 403);
            }
            return back()->with('error', $message);
        }

        try {
            $request->validate([
                'book_title' => 'required|string|max:255',
                'author' => 'nullable|string|max:255',
                'isbn' => 'nullable|string|max:30',
                'publisher' => 'nullable|string|max:255',
                'reason' => 'nullable|string|max:1000',
            ]);

            $bookRequest = LibraryBookRequest::create([
                'school_id' => $schoolId,
                'member_id' => $member->id,
                'book_title' => $request->book_title,
                'author' => $request->author,
                'isbn' => $request->isbn,
                'publisher' => $request->publisher,
                'reason' => $request->reason,
                'status' => 'pending',
            ]);

            $message = 'Book request submitted successfully.';

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'request' => $bookRequest
                ]);
            }

            return back()->with('success', $message);

        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors()
                ], 422);
            }
            throw $e;

        } catch (\Exception $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to submit request: ' . $e->getMessage()
                ], 500);
            }
            return back()->with('error', 'Failed to submit request: ' . $e->getMessage());
        }
    }

    public function reviewBookRequest(Request $request, int $id)
    {
        $schoolId = session('LoggedSchool');
        $userId = session('LoggedUser');

        try {
            $req = LibraryBookRequest::where('school_id', $schoolId)->findOrFail($id);

            $request->validate([
                'status' => 'required|in:approved,rejected,fulfilled',
                'admin_notes' => 'nullable|string|max:500'
            ]);

            $oldStatus = $req->status;

            $req->update([
                'status' => $request->status,
                'admin_notes' => $request->admin_notes,
                'reviewed_by' => $userId,
            ]);

            $statusMessages = [
                'approved' => 'Request approved successfully.',
                'rejected' => 'Request rejected.',
                'fulfilled' => 'Request marked as fulfilled.'
            ];

            $message = $statusMessages[$request->status] ?? 'Request updated successfully.';

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'old_status' => $oldStatus,
                    'new_status' => $request->status,
                    'request' => $req
                ]);
            }

            return back()->with('success', $message);

        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors()
                ], 422);
            }
            throw $e;

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Book request not found.'
                ], 404);
            }
            return back()->with('error', 'Book request not found.');

        } catch (\Exception $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update request: ' . $e->getMessage()
                ], 500);
            }
            return back()->with('error', 'Failed to update request: ' . $e->getMessage());
        }
    }

    // ─────────────────────────────────────────────────────────────────────
    // REPORTS
    // ─────────────────────────────────────────────────────────────────────

    public function reports()
    {
        $schoolId = session('LoggedSchool');

        $overdueBooks = LibraryBorrowing::where('school_id', $schoolId)
            ->whereIn('status', ['borrowed', 'overdue'])
            ->where('due_date', '<', now())
            ->with('book', 'member')
            ->orderBy('due_date')
            ->get();

        $popularBooks = LibraryBook::forSchool($schoolId)
            ->withCount('borrowings')
            ->orderByDesc('borrowings_count')
            ->with('author', 'category')
            ->take(20)->get();

        $finesReport = LibraryFine::where('school_id', $schoolId)
            ->selectRaw('status, COUNT(*) as count, SUM(amount) as total')
            ->groupBy('status')->get();

        $memberActivity = LibraryMember::forSchool($schoolId)
            ->withCount(['borrowings', 'activeBorrowings'])
            ->withSum('fines as total_fines', 'amount')
            ->orderByDesc('borrowings_count')
            ->take(20)->get();

        $monthlyStats = LibraryBorrowing::where('school_id', $schoolId)
            ->selectRaw("DATE_FORMAT(borrow_date, '%Y-%m') as month, COUNT(*) as borrowed, SUM(status='returned') as returned")
            ->groupBy('month')
            ->orderByDesc('month')
            ->take(12)->get()->reverse()->values();

        $categoryUsage = LibraryBorrowing::where('library_borrowings.school_id', $schoolId)
            ->join('library_books', 'library_borrowings.book_id', '=', 'library_books.id')
            ->join('library_categories', 'library_books.category_id', '=', 'library_categories.id')
            ->selectRaw('library_categories.name, library_categories.color, COUNT(*) as count')
            ->groupBy('library_categories.id', 'library_categories.name', 'library_categories.color')
            ->orderByDesc('count')->take(8)->get();

        return view('Library.reports', compact(
            'overdueBooks',
            'popularBooks',
            'finesReport',
            'memberActivity',
            'monthlyStats',
            'categoryUsage'
        ));
    }

    // ─────────────────────────────────────────────────────────────────────
    // CATALOGUE (Student/Teacher portal)
    // ─────────────────────────────────────────────────────────────────────

    public function catalogue(Request $request)
    {
        $schoolId = session('LoggedSchool');
        $member = $this->getSessionMember($schoolId);

        $query = LibraryBook::forSchool($schoolId)->active()->with('author', 'category', 'subject');

        // With this (inline, no model scope needed as fallback):
        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function ($q) use ($term) {
                $q->where('title', 'like', "%{$term}%")
                    ->orWhere('isbn', 'like', "%{$term}%")
                    ->orWhere('publisher', 'like', "%{$term}%")
                    ->orWhereHas('author', fn($a) => $a->where('name', 'like', "%{$term}%"))
                    ->orWhereHas('category', fn($c) => $c->where('name', 'like', "%{$term}%"));
            });
        }
        if ($request->filled('category_id'))
            $query->where('category_id', $request->category_id);
        if ($request->filled('subject_id'))
            $query->where('subject_id', $request->subject_id);
        if ($request->filled('availability') && $request->availability === 'available') {
            $query->where('available_copies', '>', 0);
        }
        if ($request->filled('has_ebook'))
            $query->where('has_ebook', true);

        $books = $query->orderBy('title')->paginate(16)->appends($request->all());
        $categories = LibraryCategory::where('school_id', $schoolId)->active()->withCount('books')->orderBy('name')->get();
        $subjects = LibrarySubject::where('school_id', $schoolId)->active()->orderBy('name')->get();

        // Recommendations based on borrowing history
        $recommendations = collect();
        if ($member) {
            $borrowedCategoryIds = LibraryBorrowing::where('member_id', $member->id)
                ->join('library_books', 'library_borrowings.book_id', '=', 'library_books.id')
                ->pluck('library_books.category_id')
                ->unique();
            if ($borrowedCategoryIds->isNotEmpty()) {
                $borrowedBookIds = LibraryBorrowing::where('member_id', $member->id)->pluck('book_id');
                $recommendations = LibraryBook::forSchool($schoolId)->active()
                    ->whereIn('category_id', $borrowedCategoryIds)
                    ->whereNotIn('id', $borrowedBookIds)
                    ->with('author', 'category')
                    ->inRandomOrder()->take(6)->get();
            }
        }

        $settings = LibrarySetting::forSchool($schoolId);

        return view('Library.catalogue', compact(
            'books',
            'categories',
            'subjects',
            'member',
            'recommendations',
            'settings'
        ));
    }

    public function myBorrowings()
    {
        $schoolId = session('LoggedSchool');
        $member = $this->getSessionMember($schoolId);
        if (!$member)
            return redirect()->route('library.catalogue')->with('error', 'You are not a library member.');

        $borrowings = LibraryBorrowing::where('member_id', $member->id)
            ->with('book')->orderByDesc('borrow_date')->paginate(20);
        $myFines = LibraryFine::where('member_id', $member->id)->with('borrowing.book')->get();
        $myReservations = LibraryReservation::where('member_id', $member->id)->with('book')
            ->whereIn('status', ['pending', 'ready'])->get();

        return view('Library.my-borrowings', compact('member', 'borrowings', 'myFines', 'myReservations'));
    }

    // ─────────────────────────────────────────────────────────────────────
    // SETTINGS
    // ─────────────────────────────────────────────────────────────────────

    public function settings()
    {
        $schoolId = session('LoggedSchool');
        $settings = LibrarySetting::forSchool($schoolId);
        return view('Library.settings', compact('settings'));
    }

    public function updateSettings(Request $request)
    {
        $schoolId = session('LoggedSchool');

        try {
            $settings = LibrarySetting::forSchool($schoolId);

            $request->validate([
                'fine_per_day' => 'required|numeric|min:0',
                'student_max_books' => 'required|integer|min:1',
                'teacher_max_books' => 'required|integer|min:1',
                'student_loan_days' => 'required|integer|min:1',
                'teacher_loan_days' => 'required|integer|min:1',
                'max_renewals' => 'required|integer|min:0',
                'enable_reservations' => 'nullable|boolean',
                'enable_ebooks' => 'nullable|boolean',
                'enable_recommendations' => 'nullable|boolean',
            ]);

            $settings->update([
                'fine_per_day' => $request->fine_per_day,
                'student_max_books' => $request->student_max_books,
                'teacher_max_books' => $request->teacher_max_books,
                'student_loan_days' => $request->student_loan_days,
                'teacher_loan_days' => $request->teacher_loan_days,
                'max_renewals' => $request->max_renewals,
                'enable_reservations' => $request->boolean('enable_reservations'),
                'enable_ebooks' => $request->boolean('enable_ebooks'),
                'enable_recommendations' => $request->boolean('enable_recommendations'),
            ]);

            $message = 'Library settings saved successfully.';

            // Check if it's an AJAX request
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'settings' => $settings->fresh()
                ]);
            }

            return back()->with('success', $message);

        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors()
                ], 422);
            }
            throw $e;

        } catch (\Exception $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update settings: ' . $e->getMessage()
                ], 500);
            }

            return back()->with('error', 'Failed to update settings: ' . $e->getMessage());
        }
    }
    // ─────────────────────────────────────────────────────────────────────
    // HELPERS
    // ─────────────────────────────────────────────────────────────────────

    private function getSessionMember(int $schoolId): ?LibraryMember
    {
        // Check if logged-in user is a student or teacher with a library membership
        if (session('LoggedStudent')) {
            $studentId = session('LoggedStudent');
            return LibraryMember::where('school_id', $schoolId)
                ->where('member_type', 'student')
                ->where('member_id', $studentId)
                ->first();
        }
        if (session('LoggedTeacher')) {
            $teacherId = session('LoggedTeacher');
            return LibraryMember::where('school_id', $schoolId)
                ->where('member_type', 'teacher')
                ->where('member_id', $teacherId)
                ->first();
        }
        return null;
    }
}