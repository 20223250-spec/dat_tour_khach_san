<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    private const ADMIN_WORKSPACE_ANCHOR = '#quan-tri-noi-bo';

    public function dashboard()
    {
        return redirect()->to($this->adminWorkspaceUrl('bao-cao'));
    }

    public function bookings(Request $request)
    {
        return redirect()->to($this->adminWorkspaceUrl('bao-cao', array_filter([
            'report_status' => $request->status,
            'report_start_date' => $request->start_date,
            'report_end_date' => $request->end_date,
        ])));
    }

    public function tours(Request $request)
    {
        return redirect()->to($this->adminWorkspaceUrl('bao-cao'));
    }

    public function exportBookings(Request $request)
    {
        $query = Booking::with(['user', 'tour']);

        $status = $request->input('status', $request->input('report_status'));
        $startDate = $request->input('start_date', $request->input('report_start_date'));
        $endDate = $request->input('end_date', $request->input('report_end_date'));

        if ($status) {
            $query->where('status', $status);
        }

        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
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
    private function adminWorkspaceUrl(string $tab, array $query = []): string
    {
        return route('home', array_merge($query, ['admin_active_tab' => $tab])) . self::ADMIN_WORKSPACE_ANCHOR;
    }
}
