<?php
/**
 * AttendanceExport Class
 * 
 * This class handles the export of attendance data in various formats (CSV, PDF)
 */

class AttendanceExport {
    private $event;
    private $registrations;
    private $format;
    
    /**
     * Constructor
     * 
     * @param array $event Event data
     * @param array $registrations Registration data
     * @param string $format Export format (csv, pdf)
     */
    public function __construct($event, $registrations, $format = 'csv') {
        $this->event = $event;
        $this->registrations = $registrations;
        $this->format = strtolower($format);
    }
    
    /**
     * Export attendance data in the specified format
     */
    public function export() {
        switch ($this->format) {
            case 'pdf':
                $this->exportPdf();
                break;
                
            case 'csv':
            default:
                $this->exportCsv();
                break;
        }
    }
    
    /**
     * Export attendance data as CSV
     */
    private function exportCsv() {
        // Sanitize filename
        $filename = 'attendance_' . $this->sanitizeFilename($this->event['title']) . '_' . date('Y-m-d') . '.csv';
        
        // Set headers for CSV download
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        // Create output stream
        $output = fopen('php://output', 'w');
        
        // Add BOM for Excel to recognize UTF-8
        fputs($output, "\xEF\xBB\xBF");
        
        // Add header row
        fputcsv($output, [
            'ID', 
            'Participant Name', 
            'Email', 
            'Department', 
            'Registration Type', 
            'Team Name', 
            'Team Members', 
            'Registration Date', 
            'Attendance'
        ]);
        
        // Add data rows
        foreach ($this->registrations as $reg) {
            // Format team members info if applicable
            $membersInfo = '';
            if ($reg['members']) {
                $members = json_decode($reg['members'], true);
                if (is_array($members)) {
                    $memberNames = array_column($members, 'name');
                    $membersInfo = implode(', ', $memberNames);
                }
            }
            
            fputcsv($output, [
                $reg['id'],
                $reg['user_name'],
                $reg['user_email'],
                $reg['user_department'],
                $reg['team_based'] ? 'Team' : 'Individual',
                $reg['team_name'] ?? '',
                $membersInfo,
                formatDate($reg['created_at']),
                $reg['check_in'] ? 'Present' : 'Absent'
            ]);
        }
        
        // Close the output stream
        fclose($output);
        
        // Exit to prevent any additional output
        exit();
    }
    
    /**
     * Export attendance data as PDF
     */
    private function exportPdf() {
        // Only proceed if TCPDF is available
        if (!class_exists('TCPDF')) {
            // If TCPDF is not available, fallback to CSV
            $this->exportCsv();
            return;
        }
        
        // Create new PDF document
        $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8');
        
        // Set document information
        $pdf->SetCreator('EventsPro Platform');
        $pdf->SetAuthor('EventsPro Admin');
        $pdf->SetTitle('Attendance Report - ' . $this->event['title']);
        $pdf->SetSubject('Event Attendance');
        
        // Remove header and footer
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        
        // Set margins
        $pdf->SetMargins(15, 15, 15);
        $pdf->SetAutoPageBreak(true, 15);
        
        // Add a page
        $pdf->AddPage();
        
        // Set font
        $pdf->SetFont('helvetica', 'B', 16);
        
        // Logo
        if (file_exists('../public/images/logo.png')) {
            $pdf->Image('../public/images/logo.png', 15, 15, 30, 0, 'PNG');
            $pdf->Cell(0, 15, '', 0, 1);
        }
        
        // Title
        $pdf->Cell(0, 10, 'Attendance Report', 0, 1, 'C');
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->Cell(0, 10, htmlspecialchars($this->event['title']), 0, 1, 'C');
        
        // Event details
        $pdf->SetFont('helvetica', '', 11);
        $pdf->Cell(40, 8, 'Date:', 0);
        $pdf->Cell(0, 8, formatDate($this->event['date']), 0, 1);
        $pdf->Cell(40, 8, 'Time:', 0);
        $pdf->Cell(0, 8, formatTime($this->event['time']), 0, 1);
        $pdf->Cell(40, 8, 'Venue:', 0);
        $pdf->Cell(0, 8, $this->event['venue'] . ' (Room: ' . $this->event['room_no'] . ')', 0, 1);
        
        // Calculate statistics
        $totalRegistered = count($this->registrations);
        $presentCount = 0;
        foreach ($this->registrations as $reg) {
            if ($reg['check_in']) {
                $presentCount++;
            }
        }
        $absentCount = $totalRegistered - $presentCount;
        $attendanceRate = $totalRegistered > 0 ? round(($presentCount / $totalRegistered) * 100) : 0;
        
        // Attendance statistics
        $pdf->Ln(5);
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 10, 'Attendance Statistics', 0, 1);
        $pdf->SetFont('helvetica', '', 11);
        
        // Statistics table
        $pdf->Cell(60, 8, 'Total Registered:', 0);
        $pdf->Cell(30, 8, $totalRegistered, 0, 1);
        $pdf->Cell(60, 8, 'Present:', 0);
        $pdf->Cell(30, 8, $presentCount, 0, 1);
        $pdf->Cell(60, 8, 'Absent:', 0);
        $pdf->Cell(30, 8, $absentCount, 0, 1);
        $pdf->Cell(60, 8, 'Attendance Rate:', 0);
        $pdf->Cell(30, 8, $attendanceRate . '%', 0, 1);
        
        // Attendance list
        $pdf->Ln(5);
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 10, 'Attendance List', 0, 1);
        
        // Table header
        $pdf->SetFillColor(240, 240, 240);
        $pdf->SetTextColor(0);
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(10, 7, '#', 1, 0, 'C', 1);
        $pdf->Cell(60, 7, 'Participant Name', 1, 0, 'C', 1);
        $pdf->Cell(70, 7, 'Email', 1, 0, 'C', 1);
        $pdf->Cell(45, 7, 'Attendance', 1, 1, 'C', 1);
        
        // Table data
        $pdf->SetFont('helvetica', '', 9);
        foreach ($this->registrations as $index => $reg) {
            // Alternate row colors
            $fill = ($index % 2) === 0 ? 1 : 0;
            
            $pdf->Cell(10, 7, $index + 1, 1, 0, 'C', $fill);
            $pdf->Cell(60, 7, htmlspecialchars($reg['user_name']), 1, 0, 'L', $fill);
            $pdf->Cell(70, 7, htmlspecialchars($reg['user_email']), 1, 0, 'L', $fill);
            
            // Set text color based on attendance
            if ($reg['check_in']) {
                $pdf->SetTextColor(0, 128, 0); // Green for present
                $attendance = 'Present';
            } else {
                $pdf->SetTextColor(220, 0, 0); // Red for absent
                $attendance = 'Absent';
            }
            
            $pdf->Cell(45, 7, $attendance, 1, 1, 'C', $fill);
            $pdf->SetTextColor(0); // Reset text color
        }
        
        // Footer
        $pdf->Ln(10);
        $pdf->SetFont('helvetica', 'I', 8);
        $pdf->Cell(0, 5, 'Generated on ' . date('Y-m-d H:i:s') . ' by EventsPro Platform', 0, 1, 'C');
        
        // Output the PDF
        $filename = 'attendance_' . $this->sanitizeFilename($this->event['title']) . '_' . date('Y-m-d') . '.pdf';
        $pdf->Output($filename, 'D');
        
        // Exit to prevent any additional output
        exit();
    }
    
    /**
     * Sanitize filename
     * 
     * @param string $string Input string
     * @return string Sanitized string
     */
    private function sanitizeFilename($string) {
        // Replace spaces with underscores
        $string = str_replace(' ', '_', $string);
        
        // Remove special characters
        return preg_replace('/[^A-Za-z0-9_\-]/', '', $string);
    }
}
