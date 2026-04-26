<?php

namespace App\Http\Controllers;

use App\Models\Tour;
use App\Models\Booking;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function dashboard()
    {
        // Thống kê tổng quan
        $stats = [
            'total_tours' => Tour::count(),
            'total_users' => User::count(),
            'total_bookings' => Booking::count(),
            'total_revenue' => Booking::where('status', 'completed')->sum('total_price'),
            'pending_bookings' => Booking::where('status', 'pending')->count(),
            'confirmed_bookings' => Booking::where('status', 'confirmed')->count(),
            'cancelled_bookings' => Booking::where('status', 'cancelled')->count(),
        ];

        $statusBreakdown = [
            'pending' => [
                'label' => 'Chờ xác nhận',
                'count' => $stats['pending_bookings'],
                'color' => '#f3c45f',
            ],
            'confirmed' => [
                'label' => 'Đã xác nhận',
                'count' => $stats['confirmed_bookings'],
                'color' => '#25b6a5',
            ],
            'cancelled' => [
                'label' => 'Đã hủy',
                'count' => $stats['cancelled_bookings'],
                'color' => '#ee7878',
            ],
            'completed' => [
                'label' => 'Hoàn tất',
                'count' => $stats['total_bookings'] - $stats['pending_bookings'] - $stats['confirmed_bookings'] - $stats['cancelled_bookings'],
                'color' => '#6b93ff',
            ],
        ];

        $statusTotal = array_sum(array_column($statusBreakdown, 'count'));

        foreach ($statusBreakdown as $key => $item) {
            $statusBreakdown[$key]['percent'] = $statusTotal > 0
                ? round(($item['count'] / $statusTotal) * 100, 1)
                : 0;
        }

        // Doanh thu theo tháng (6 tháng gần nhất)
        $revenueByMonth = Booking::select(
                DB::raw('YEAR(created_at) as year'),
                DB::raw('MONTH(created_at) as month'),
                DB::raw('SUM(total_price) as revenue')
            )
            ->where('status', 'completed')
            ->where('created_at', '>=', now()->subMonths(6))
            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get();

        // Top tours được đặt nhiều nhất
        $topTours = Tour::withCount('bookings')
            ->orderBy('bookings_count', 'desc')
            ->limit(5)
            ->get();

        // Đơn đặt gần đây
        $recentBookings = Booking::with(['user', 'tour'])
            ->latest()
            ->limit(10)
            ->get();

        return view('admin.reports.dashboard', compact(
            'stats',
            'statusBreakdown',
            'revenueByMonth',
            'topTours',
            'recentBookings'
        ));
    }

    public function bookings(Request $request)
    {
        $query = Booking::with(['user', 'tour']);

        // Filter by status
        if ($request->status) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->start_date && $request->end_date) {
            $query->whereBetween('created_at', [$request->start_date, $request->end_date]);
        }

        $bookings = $query->latest()->paginate(20);

        // Thống kê theo trạng thái
        $statusStats = Booking::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status');

        return view('admin.reports.bookings', compact('bookings', 'statusStats'));
    }

    public function tours(Request $request)
    {
        $tours = Tour::withCount('bookings')
            ->with(['bookings' => function($query) {
                $query->select('tour_id', 'total_price', 'status');
            }])
            ->orderBy('bookings_count', 'desc')
            ->paginate(20);

        // Thống kê doanh thu theo tour
        $tourRevenue = [];
        foreach ($tours as $tour) {
            $tourRevenue[$tour->id] = $tour->bookings->where('status', 'completed')->sum('total_price');
        }

        return view('admin.reports.tours', compact('tours', 'tourRevenue'));
    }

    public function exportBookings(Request $request)
    {
        $query = Booking::with(['user', 'tour']);

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->start_date && $request->end_date) {
            $query->whereBetween('created_at', [$request->start_date, $request->end_date]);
        }

        $bookings = $query->get();

        // Generate CSV content
        $csv = "ID,Người dùng,Tour,Số người,Tổng tiền,Trạng thái,Ngày đặt\n";

        foreach ($bookings as $booking) {
            $csv .= sprintf(
                "%d,%s,%s,%d,%d,%s,%s\n",
                $booking->id,
                $booking->user->name,
                $booking->tour->name,
                $booking->number_of_people,
                $booking->total_price,
                $booking->status,
                $booking->created_at->format('Y-m-d H:i:s')
            );
        }

        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="bookings_' . date('Y-m-d') . '.csv"');
    }
}
