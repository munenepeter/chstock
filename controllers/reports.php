<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../database/database.php';

class ReportsController {
    private $db;

    public function __construct($db = null) {
        $this->db = $db ?? (new Database())->getConnection();
    }

    public function getStockLevelsReport() {
        $query = "
            SELECT 
                si.name,
                si.category,
                si.unit,
                si.stock_level,
                si.reorder_level,
                s.name as supplier_name,
                si.expiry_date
            FROM stock_items si
            LEFT JOIN suppliers s ON si.supplier_id = s.id
            ORDER BY si.category, si.name
        ";
        return $this->db->query($query)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTransactionsReport($startDate = null, $endDate = null) {
        $params = [];
        $whereClause = "";
        
        if ($startDate && $endDate) {
            $whereClause = " WHERE transaction_date BETWEEN :start_date AND :end_date";
            $params = [
                'start_date' => $startDate,
                'end_date' => $endDate
            ];
        }

        $query = "
            SELECT 
                st.transaction_date,
                st.transaction_type,
                st.quantity,
                si.name as item_name,
                si.category,
                st.issued_to,
                st.received_from
            FROM stock_transactions st
            JOIN stock_items si ON st.stock_id = si.id
            $whereClause
            ORDER BY st.transaction_date DESC
        ";

        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getLowStockReport() {
        $query = "
            SELECT 
                si.name,
                si.category,
                si.unit,
                si.stock_level,
                si.reorder_level,
                s.name as supplier_name
            FROM stock_items si
            LEFT JOIN suppliers s ON si.supplier_id = s.id
            WHERE si.stock_level <= si.reorder_level
            ORDER BY (si.reorder_level - si.stock_level) DESC
        ";
        return $this->db->query($query)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getExpiringItemsReport() {
        $query = "
            SELECT 
                si.name,
                si.category,
                si.unit,
                si.stock_level,
                si.expiry_date,
                s.name as supplier_name
            FROM stock_items si
            LEFT JOIN suppliers s ON si.supplier_id = s.id
            WHERE si.expiry_date IS NOT NULL 
            AND si.expiry_date <= DATE('now', '+30 days')
            AND si.stock_level > 0
            ORDER BY si.expiry_date ASC
        ";
        return $this->db->query($query)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function generatePDF($reportType, $data, $title) {
        // Create new PDF document
        $pdf = new \TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // Remove default header/footer
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        // Set document information
        $pdf->SetCreator('Lab Stock Management System');
        $pdf->SetAuthor('Lab Stock Management');
        $pdf->SetTitle($title);

        // Set default monospaced font
        $pdf->SetDefaultMonospacedFont('courier');

        // Set margins
        $pdf->SetMargins(15, 15, 15);

        // Set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, 15);

        // Add a page
        $pdf->AddPage();

        // Set font
        $pdf->SetFont('helvetica', 'B', 16);

        // Add title
        $pdf->Cell(0, 10, $title, 0, 1, 'C');
        $pdf->Ln(10);

        // Set font for table
        $pdf->SetFont('helvetica', '', 10);

        // Build the table HTML based on report type
        $html = '<table border="1" cellpadding="5" cellspacing="0" style="width: 100%;">';
        
        // Add table headers
        $html .= '<tr style="background-color: #f8f9fa; font-weight: bold;">';
        switch ($reportType) {
            case 'stock_levels':
                $html .= '<th>Item Name</th>
                         <th>Category</th>
                         <th>Unit</th>
                         <th>Stock Level</th>
                         <th>Reorder Level</th>
                         <th>Supplier</th>
                         <th>Expiry Date</th>';
                break;
                
            case 'transactions':
                $html .= '<th>Date</th>
                         <th>Type</th>
                         <th>Item</th>
                         <th>Category</th>
                         <th>Quantity</th>
                         <th>From/To</th>';
                break;
                
            case 'low_stock':
                $html .= '<th>Item Name</th>
                         <th>Category</th>
                         <th>Unit</th>
                         <th>Current Stock</th>
                         <th>Reorder Level</th>
                         <th>Supplier</th>';
                break;
                
            case 'expiring':
                $html .= '<th>Item Name</th>
                         <th>Category</th>
                         <th>Unit</th>
                         <th>Stock Level</th>
                         <th>Expiry Date</th>
                         <th>Supplier</th>';
                break;
        }
        $html .= '</tr>';
        
        // Add table data
        foreach ($data as $row) {
            $html .= '<tr>';
            switch ($reportType) {
                case 'stock_levels':
                    $html .= sprintf(
                        '<td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td>',
                        htmlspecialchars($row['name']),
                        htmlspecialchars($row['category']),
                        htmlspecialchars($row['unit']),
                        $row['stock_level'],
                        $row['reorder_level'],
                        htmlspecialchars($row['supplier_name']),
                        $row['expiry_date']
                    );
                    break;
                    
                case 'transactions':
                    $partyInfo = $row['transaction_type'] === 'received' ? 
                                $row['received_from'] : $row['issued_to'];
                    $html .= sprintf(
                        '<td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td>',
                        date('Y-m-d H:i', strtotime($row['transaction_date'])),
                        ucfirst($row['transaction_type']),
                        htmlspecialchars($row['item_name']),
                        htmlspecialchars($row['category']),
                        $row['quantity'],
                        htmlspecialchars($partyInfo)
                    );
                    break;
                    
                case 'low_stock':
                    $html .= sprintf(
                        '<td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td>',
                        htmlspecialchars($row['name']),
                        htmlspecialchars($row['category']),
                        htmlspecialchars($row['unit']),
                        $row['stock_level'],
                        $row['reorder_level'],
                        htmlspecialchars($row['supplier_name'])
                    );
                    break;
                    
                case 'expiring':
                    $html .= sprintf(
                        '<td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td>',
                        htmlspecialchars($row['name']),
                        htmlspecialchars($row['category']),
                        htmlspecialchars($row['unit']),
                        $row['stock_level'],
                        $row['expiry_date'],
                        htmlspecialchars($row['supplier_name'])
                    );
                    break;
            }
            $html .= '</tr>';
        }
        
        $html .= '</table>';

        // Add generation date
        $pdf->Ln(5);
        $pdf->SetFont('helvetica', 'I', 8);
        $pdf->Cell(0, 10, 'Generated on: ' . date('Y-m-d H:i:s'), 0, 1, 'R');

        // Print the HTML content
        $pdf->writeHTML($html, true, false, true, false, '');

        // Close and output PDF document
        return $pdf->Output($title . '.pdf', 'I');
    }

    private function buildReportHTML($reportType, $data) {
        $html = '<table border="1" cellpadding="5">
                <thead><tr style="background-color: #f1f1f1;">';
        
        switch ($reportType) {
            case 'stock_levels':
                $html .= '<th>Item Name</th>
                         <th>Category</th>
                         <th>Unit</th>
                         <th>Stock Level</th>
                         <th>Reorder Level</th>
                         <th>Supplier</th>
                         <th>Expiry Date</th>';
                break;
                
            case 'transactions':
                $html .= '<th>Date</th>
                         <th>Type</th>
                         <th>Item</th>
                         <th>Category</th>
                         <th>Quantity</th>
                         <th>From/To</th>';
                break;
                
            case 'low_stock':
                $html .= '<th>Item Name</th>
                         <th>Category</th>
                         <th>Unit</th>
                         <th>Current Stock</th>
                         <th>Reorder Level</th>
                         <th>Supplier</th>';
                break;
                
            case 'expiring':
                $html .= '<th>Item Name</th>
                         <th>Category</th>
                         <th>Unit</th>
                         <th>Stock Level</th>
                         <th>Expiry Date</th>
                         <th>Supplier</th>';
                break;
        }
        
        $html .= '</tr></thead><tbody>';
        
        foreach ($data as $row) {
            $html .= '<tr>';
            switch ($reportType) {
                case 'stock_levels':
                    $html .= sprintf(
                        '<td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td>',
                        htmlspecialchars($row['name']),
                        htmlspecialchars($row['category']),
                        htmlspecialchars($row['unit']),
                        $row['stock_level'],
                        $row['reorder_level'],
                        htmlspecialchars($row['supplier_name']),
                        $row['expiry_date']
                    );
                    break;
                    
                case 'transactions':
                    $partyInfo = $row['transaction_type'] === 'received' ? 
                                $row['received_from'] : $row['issued_to'];
                    $html .= sprintf(
                        '<td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td>',
                        date('Y-m-d H:i', strtotime($row['transaction_date'])),
                        ucfirst($row['transaction_type']),
                        htmlspecialchars($row['item_name']),
                        htmlspecialchars($row['category']),
                        $row['quantity'],
                        htmlspecialchars($partyInfo)
                    );
                    break;
                    
                case 'low_stock':
                    $html .= sprintf(
                        '<td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td>',
                        htmlspecialchars($row['name']),
                        htmlspecialchars($row['category']),
                        htmlspecialchars($row['unit']),
                        $row['stock_level'],
                        $row['reorder_level'],
                        htmlspecialchars($row['supplier_name'])
                    );
                    break;
                    
                case 'expiring':
                    $html .= sprintf(
                        '<td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td>',
                        htmlspecialchars($row['name']),
                        htmlspecialchars($row['category']),
                        htmlspecialchars($row['unit']),
                        $row['stock_level'],
                        $row['expiry_date'],
                        htmlspecialchars($row['supplier_name'])
                    );
                    break;
            }
            $html .= '</tr>';
        }
        
        $html .= '</tbody></table>';
        return $html;
    }
}
