<?php

require_once($_SERVER['DOCUMENT_ROOT'] . 'selnate/controllers/lib/fpdf/fpdf.php');

class PDF extends FPDF {
    private $createDate = "";

    function Header() {
        //$server_host = 'C:/wamp64/www/';
        //$server_host = '/home/a6683804/public_html/test/';

        //$this->Image($_SERVER['DOCUMENT_ROOT'] . 'selnate/views/images/logo.png', 10, 6, 30);
        //$this->Ln(10);
        $this->SetFont('Arial', 'I', 12);
        $x = $this->GetX();
        $y = $this->GetY();
        $this->Image($_SERVER['DOCUMENT_ROOT'] . 'selnate/views/images/logo.png', 10, 6, 30);
        $x2 = $this->GetX();
        $this->SetXY($x2 - $x, $y);
        $this->Cell(0, 6, "Date: " . $this->createDate, 0, 0, 'R', false);
        $this->Ln(15);
        
        
    }

    function Footer() {
        // Position at 1.5 cm from bottom
        $this->SetY(-15);
        // Arial italic 8
        $this->SetFont('Arial', 'I', 10);
        // Text color in gray
        $this->SetTextColor(128);
        // Page number
        $this->Cell(0, 10, 'Page ' . $this->PageNo(), 0, 0, 'C');
    }

    function EssayTitle($studentName, $timeSpent, $teacherName, $wordCount, $instructions, $prompt) {
        
        // Background color
        $this->SetFillColor(255, 255, 255);
        
        // **** WRITING 1st LINE OF ESSAY'S INFORMATION. *****
        //Writing STUDENT
        $this->Ln(5);        
        $y = $this->GetY();        
        $this->SetFont('Arial', 'B', 13); //Bold
        $this->Cell(0, 6, "Student:", 0, 1, 'L', true);        
        $this->SetXY(30,$y); //Space between "Student:" and Name        
        $this->SetFont('Arial', '', 13); //NOT Bold
        $this->Cell(0, 6, $studentName, 0, 1, 'L', false);
        
        //Writing TIME SPENT
        $this->SetXY(108,$y); //Space between "Time Spent:" and xx:xx
        $this->SetFont('Arial', 'B', 13);
        $this->Cell(0, 6, "Time Spent:", 0, 0, 'C', false);        
        $this->SetFont('Arial', '', 13);
        $this->Cell(0, 6, "$timeSpent minutes", 0, 0, 'R', false);
         
        $this->Ln(10);

        // **** WRITING 2nd LINE OF ESSAY'S INFORMATION. *****
        // Writing TEACHER
        //$x = $this->GetX();
        $y = $this->GetY();
        $this->SetFont('Arial', 'B', 13); //Bold
        $this->Cell(0, 6, "Teacher:", 0, 1, 'L', true);
        $this->SetXY(30,$y); //Space between "Teacher:" and Name   
        $this->SetFont('Arial', '', 13); //NOT Bold
        $this->Cell(0, 6, $teacherName, 0, 1, 'L', false);
        
        //Writting WORD COUNT
        $this->SetXY(111,$y); //Space between "Word Count:" and xx words
        $this->SetFont('Arial', 'B', 13);        
        $this->Cell(0, 6, "Word Count:", 0, 0, 'C', false); //Extra spaces to align 'Word Count' with 'Time Spent'.        
        $this->SetXY(168,$y); //Space between "Word Count:" and xx words
        $this->SetFont('Arial', '', 13);
        $this->Cell(0, 6, "$wordCount words", 0, 0, 'C', false);
        $this->Ln(20);

        $this->SetFont('Times', '', 13);
        //Writing Instructions and Prompt Lines of Essay's.
        $this->MultiCell(0, 6, "Instructions: $instructions", 0, 1, 'L', false);
        $this->Ln(5);
        $this->MultiCell(0, 6, "Prompt: $prompt", 0, 1, 'L', false);

        // Line break
        $this->Ln(10);
    }

    function EssayBody($file) {
        // Read text file
        $txt = $file;
        // Times 12
        $this->SetFont('Times', '', 13);
        // Output justified text        
        $this->MultiCell(0, 10, $txt);
    }

    function IdNameListTitle($title, $teacherFullName, $instructions, $prompt) {
        // Arial 12
        $this->SetFont('Arial', '', 13);
        // Background color
        $this->SetFillColor(255, 255, 255);

        //Writing Instructions and Prompt Lines of the list.
        $this->Ln(5);
        $this->Cell(0, 6, "Teacher: $teacherFullName", 0, 1, 'L', false);
        $this->Ln(10);
        $this->MultiCell(0, 6, "Instructions: $instructions", 0, 1, 'L', false);
        $this->Ln(3);
        $this->MultiCell(0, 6, "Prompt: $prompt", 0, 1, 'L', false);
        $this->Ln(10);

        //Writing the Title of the list.
        $x = $this->GetX();
        $y = $this->GetY();
        $this->Cell(0, 10, $title, 0, 0, 'C');
        $x2 = $this->GetX();

        // Line break
        $this->Ln(15);
    }

    function ListBody($idNameList) {
        foreach ($idNameList as $key => $value) {
            $this->SetX(55);
            $this->SetFillColor(219, 221, 219);
            $this->Cell(10, 10, $key, 0, 0, 'C', TRUE);
            $this->Cell(90, 10, $value, 0, 0, 'L', TRUE);
            $this->Ln(15);
        }
    }

    function PrintEssay($studentName, $duration, $teacherName, $wordCount, $instructions, $prompt, $essayContent, $creationDate) {
        $this->createDate = $creationDate;
        $this->AddPage();
        $this->EssayTitle($studentName, $duration, $teacherName, $wordCount, $instructions, $prompt);
        $this->EssayBody($essayContent);
    }

    function PrintIDNameList($idNameList, $teacherFullName, $instructions, $prompt, $creationDate) {
        $lv_title = "ID/NAME LIST";
        $this->createDate = $creationDate;
        $this->AddPage();
        $this->IdNameListTitle($lv_title, $teacherFullName, $instructions, $prompt);
        $this->ListBody($idNameList);
    }

}
