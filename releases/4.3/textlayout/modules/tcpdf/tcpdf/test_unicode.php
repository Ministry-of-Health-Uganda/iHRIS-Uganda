<?php
//============================================================+
// File name   : test_unicode.php
// Begin       : 2004-07-14
// Last Update : 2008-02-12
// 
// Description : Test page fot TCPDF class
// 
// Author: Nicola Asuni
// 
// (c) Copyright:
//               Nicola Asuni
//               Tecnick.com s.r.l.
//               Via Della Pace, 11
//               09044 Quartucciu (CA)
//               ITALY
//               www.tecnick.com
//               info@tecnick.com
//============================================================+

/**
 * Create PDF TEST document (UTF-8 version)
 * @package com.tecnick.tcpdf
 * @abstract TCPDF - unicode test.
 * @author Nicola Asuni
 * @copyright 2004-2008 Nicola Asuni - Tecnick.com S.r.l (www.tecnick.com) Via Della Pace, 11 - 09044 - Quartucciu (CA) - ITALY - www.tecnick.com - info@tecnick.com
 * @link http://tcpdf.sourceforge.net
 * @license http://www.gnu.org/copyleft/lesser.html LGPL
 * @since 2004-07-14
 */

$doc_title = "test title";
$doc_subject = "test description";
$doc_keywords = "test keywords";
$htmlcontent = "&lt; € &euro; &#8364; &amp; è &egrave; &copy; &gt;<br /><h1>heading 1</h1><h2>heading 2</h2><h3>heading 3</h3><h4>heading 4</h4><h5>heading 5</h5><h6>heading 6</h6>ordered list:<br /><ol><li><b>bold text</b></li><li><i>italic text</i></li><li><u>underlined text</u></li><li><a href=\"http://www.tecnick.com\" dir=\"ltr\">link to http://www.tecnick.com</a></li><li>test break<br />second line 12,34.56 text text<br />third line</li><li dir=\"rtl\">REVERSED Right</li><li dir=\"ltr\">REVERSED Left</li><li><font size=\"+3\">font + 3</font></li><li><small>small text</small></li><li>normal <sub>subscript</sub> <sup>superscript</sup></li></ul><hr />table:<br /><table border=\"1\" cellspacing=\"1\" cellpadding=\"1\"><tr><th>#</th><th>A</th><th>B</th></tr><tr><th>1</th><td bgcolor=\"#cccccc\" align=\"center\">A1</td><td>B1</td></tr><tr><th>2</th><td>A2 € &euro; &#8364; &amp; è &egrave; </td><td>&nbsp;</td></tr><tr><th>3</th><td>A3</td><td><font color=\"#FF0000\">B3</font></td></tr></table><hr />images:<br /><img src=\"images/logo_example.png\" alt=\"test alt attribute\" width=\"100\" height=\"100\" border=\"0\" align=\"top\" /><img src=\"images/logo_example.gif\" alt=\"test alt attribute\" width=\"100\" height=\"100\" border=\"0\" align=\"top\" /><img src=\"images/logo_example.jpg\" alt=\"test alt attribute\" width=\"100\" height=\"100\" border=\"0\" />";

require_once('config/lang/eng.php');
require_once('tcpdf.php');

//create new PDF document (document units are set by default to millimeters)
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true); 

// Set PDF protection (RSA 40bit encryption)
// uncomment the following line to enable document encryption
//$pdf->SetProtection(array('print'));

// set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor(PDF_AUTHOR);
$pdf->SetTitle($doc_title);
$pdf->SetSubject($doc_subject);
$pdf->SetKeywords($doc_keywords);

$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);

//set margins
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
//set auto page breaks
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO); //set image scale factor

$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

$pdf->setLanguageArray($l); //set language items


//initialize document
$pdf->AliasNbPages();

$pdf->AddPage();
$pdf->Bookmark('Variuos Tests', 0, 0);

// set barcode
$pdf->SetBarcode(date("Y-m-d H:i:s", time()));

$pdf->Bookmark('Bidirectional string', 1, -1);
$pdf->SetFont("FreeSerif", "", 12);
$pdf->writeHTML("The words &#8220;<span dir=\"rtl\">&#1502;&#1494;&#1500; [mazel] &#1496;&#1493;&#1489; [tov]</span>&#8221; mean &#8220;Congratulations!&#8221;", true, 0);

// Image example
$pdf->Image("images/logo_example.gif", 100,100, 20, 20, '', 'http://www.tecnick.com', '');

// output some HTML code
$pdf->Bookmark('HTML code', 1, -1);
$pdf->writeHTML($htmlcontent, true, 0);

// output two html columns
$first_column_width = 80;
$current_y_position = $pdf->getY();
$pdf->writeHTMLCell($first_column_width, 0, 0, $current_y_position, "<b>hello</b>", 0, 0, 0); 
$pdf->writeHTMLCell(0, 0, $first_column_width, $current_y_position, "<i>world</i>", 0, 1, 0); 

// output some content
$pdf->Bookmark('Cell', 1, -1);
$pdf->SetFont("vera", "BI", 20);
$pdf->Cell(0,10,"TEST Bold-Italic Cell",1,1,'C');

$pdf->AddPage();
$pdf->Bookmark('Cell Stretch', 0, 0);

// test Cell stretching
$pdf->Cell(0, 10, "TEST CELL STRETCH: no stretch", 1, 1, 'C', 0, '', 0);
$pdf->Cell(0, 10, "TEST CELL STRETCH: scaling", 1, 1, 'C', 0, '', 1);
$pdf->Cell(0, 10, "TEST CELL STRETCH: force scaling", 1, 1, 'C', 0, '', 2);
$pdf->Cell(0, 10, "TEST CELL STRETCH: spacing", 1, 1, 'C', 0, '', 3);
$pdf->Cell(0, 10, "TEST CELL STRETCH: force spacing", 1, 1, 'C', 0, '', 4);

$pdf->AddPage();
$pdf->Bookmark('Text Function', 0, 0);

// output some content
$pdf->SetFont("vera", "U", 20);
$pdf->text(50,50,"TEST Text Function");

// output some UTF-8 test content
$pdf->AddPage();
$pdf->Bookmark('External UTF-8 text file', 0, 0);

$pdf->SetFont("FreeSerif", "", 12);
$utf8text = file_get_contents("utf8test.txt", false); // get utf-8 text form file
$pdf->SetFillColor(230, 240, 255, true);
$pdf->Write(5,$utf8text, '', 1);

// remove page header/footer
$pdf->setPrintHeader(false);

// Two HTML columns test
$pdf->AddPage("L");
$pdf->Bookmark('Two HTML Columns', 0, 0);

$pdf->setPrintFooter(false);

$pdf->Ln();
$right_column = "<b>right column</b> right column right column right column right column
right column right column right column right column right column right column
right column right column right column right column right column right column";
$left_column = "<b>left column</b> left column left column left column left column left
column left column left column left column left column left column left column
left column left column left column left column left column left column left
column
left column left column left column left column left
column left column left column left column left column left column left column
left column left column left column left column left column left column left
column left column left column left column left column left
column left column left column left column left column left column left column
left column left column left column left column left column left column left
column
left column left column left column left column left
column left column left column left column left column left column left column
left column left column left column left column left column left column left
column left column left column left column left column left
column left column left column left column left column left column left column
left column left column left column left column left column left column left
column
left column left column left column left column left
column left column left column left column left column left column left column
left column left column left column left column left column left column left
column left column left column left column left column left
column left column left column left column left column left column left column
left column left column left column left column left column left column left
column
left column left column left column left column left
column left column left column left column left column left column left column
left column left column left column left column left column left column left
column left column left column left column left column left
column left column left column left column left column left column left column
left column left column left column left column left column left column left
column
left column left column left column left column left
column left column left column left column left column left column left column
left column left column left column left column left column left column left
column left column left column left column left column left
column left column left column left column left column left column left column
left column left column left column left column left column left column left
column
left column left column left column left column left
column left column left column left column left column left column left column
left column left column left column left column left column left column left
column left column left column left column left column left
column left column left column left column left column left column left column
left column left column left column left column left column left column left
column
left column left column left column left column left
column left column left column left column left column left column left column
left column left column left column left column left column left column left
column left column left column left column left column left
column left column left column left column left column left column left column
left column left column left column left column left column left column left
column";
$first_column_width = 80;
$second_column_width = 80;
$column_space = 20; 
$current_y_position = $pdf->getY();
$pdf->writeHTMLCell($first_column_width, 0, 0, $current_y_position, $left_column, 1, 0, 0, true);
$pdf->writeHTMLCell($second_column_width, 0, 0, 0, $right_column, 1, 1, 0, true);

// reset pointer to the last page
$pdf->lastPage();

// add page header/footer
$pdf->setPrintHeader(true);
$pdf->AddPage();
$pdf->Bookmark('Multicell', 0, 0);

$pdf->setPrintFooter(true);

// Multicell test
$pdf->MultiCell(40, 5, "A test multicell line 1\ntest multicell line 2\ntest multicell line 3", 1, 'L', 0, 0);
$pdf->MultiCell(40, 5, "B test multicell line 1\ntest multicell line 2\ntest multicell line 3", 1, 'R', 0, 1);
$pdf->MultiCell(40, 5, "C test multicell line 1\ntest multicell line 2\ntest multicell line 3", 1, 'C', 0, 0);
$pdf->MultiCell(40, 5, "D test multicell line 1\ntest multicell line 2\ntest multicell line 3", 1, 'J', 0, 2);
$pdf->MultiCell(40, 5, "E test multicell line 1\ntest multicell line 2\ntest multicell line 3", 1, 'L', 0, 1);

$pdf->MultiCell(40, 5, "F test multicell line\nF test multicell line\nF test multicell line\nF test multicell line\nF test multicell line\nF test multicell line\nF test multicell line\nF test multicell line\nF test multicell line\nF test multicell line\nF test multicell line\nF test multicell line\nF test multicell line\nF test multicell line\nF test multicell line\nF test multicell line\nF test multicell line\nF test multicell line\nF test multicell line\nF test multicell line\nF test multicell line\nF test multicell line\nF test multicell line\nF test multicell line\nF test multicell line\nF test multicell line\nF test multicell line\nF test multicell line\nF test multicell line\nF test multicell line\nF test multicell line\nF test multicell line\nF test multicell line\nF test multicell line\nF test multicell line\nF test multicell line\nF test multicell line\nF test multicell line\nF test multicell line\nF test multicell line\nF test multicell line\nF test multicell line\nF test multicell line\nF test multicell line\nF test multicell line\nF test multicell line\nF test multicell line\nF test multicell line\nF test multicell line\nF test multicell line\nF test multicell line\nF test multicell line\nF test multicell line\nF test multicell line\nF test multicell line\nF test multicell line\nF test multicell line\nF test multicell line\nF test multicell line\nF test multicell line\nF test multicell line\nF test multicell line\nF test multicell line\nF test multicell line", 1, 'L', 0, 1);
// reset pointer to the last page
$pdf->lastPage();

// --- START GRAPHIC TRANFORMATIONS TEST -------------------
// Code Provided by Moritz Wagner and Andreas Würmser
$pdf->setPrintHeader(false);
$pdf->AddPage();
$pdf->Bookmark('Transformations', 0, 0);

$pdf->setPrintFooter(false);

//Scaling
$pdf->Bookmark('Scaling', 1, 4);
$pdf->SetDrawColor(200);
$pdf->SetTextColor(200);
$pdf->Rect(50, 20, 40, 10, 'D');
$pdf->Text(50, 19, 'Scale');
$pdf->SetDrawColor(0);
$pdf->SetTextColor(0);
//Start Transformation
$pdf->StartTransform();
//Scale by 150% centered by (50,30) which is the lower left corner of the rectangle
$pdf->ScaleXY(150, 50, 30);
$pdf->Rect(50, 20, 40, 10, 'D');
$pdf->Text(50, 19, 'Scale');
//Stop Transformation
$pdf->StopTransform();

//Translation
$pdf->Bookmark('Translation', 1, 14);
$pdf->SetDrawColor(200);
$pdf->SetTextColor(200);
$pdf->Rect(125, 20, 40, 10, 'D');
$pdf->Text(125, 19, 'Translate');
$pdf->SetDrawColor(0);
$pdf->SetTextColor(0);
//Start Transformation
$pdf->StartTransform();
//Translate 7 to the right, 5 to the bottom
$pdf->Translate(7, 5);
$pdf->Rect(125, 20, 40, 10, 'D');
$pdf->Text(125, 19, 'Translate');
//Stop Transformation
$pdf->StopTransform();

//Rotation
$pdf->Bookmark('Rotation', 1, 35);
$pdf->SetDrawColor(200);
$pdf->SetTextColor(200);
$pdf->Rect(50, 50, 40, 10, 'D');
$pdf->Text(50, 49, 'Rotate');
$pdf->SetDrawColor(0);
$pdf->SetTextColor(0);
//Start Transformation
$pdf->StartTransform();
//Rotate 20 degrees counter-clockwise centered by (50,60) which is the lower left corner of the rectangle
$pdf->Rotate(20, 50, 60);
$pdf->Rect(50, 50, 40, 10, 'D');
$pdf->Text(50, 49, 'Rotate');
//Stop Transformation
$pdf->StopTransform();

//Skewing
$pdf->Bookmark('Skewing', 1, 43);
$pdf->SetDrawColor(200);
$pdf->SetTextColor(200);
$pdf->Rect(125, 50, 40, 10, 'D');
$pdf->Text(125, 49, 'Skew');
$pdf->SetDrawColor(0);
$pdf->SetTextColor(0);
//Start Transformation
$pdf->StartTransform();
//skew 30 degrees along the x-axis centered by (125,60) which is the lower left corner of the rectangle
$pdf->SkewX(30, 125, 60);
$pdf->Rect(125, 50, 40, 10, 'D');
$pdf->Text(125, 49, 'Skew');
//Stop Transformation
$pdf->StopTransform();

//Mirroring horizontally
$pdf->Bookmark('Mirroring', 1, 73);
$pdf->SetDrawColor(200);
$pdf->SetTextColor(200);
$pdf->Rect(50, 80, 40, 10, 'D');
$pdf->Text(50, 79, 'MirrorH');
$pdf->SetDrawColor(0);
$pdf->SetTextColor(0);
//Start Transformation
$pdf->StartTransform();
//mirror horizontally with axis of reflection at x-position 50 (left side of the rectangle)
$pdf->MirrorH(50);
$pdf->Rect(50, 80, 40, 10, 'D');
$pdf->Text(50, 79, 'MirrorH');
//Stop Transformation
$pdf->StopTransform();

//Mirroring vertically
$pdf->SetDrawColor(200);
$pdf->SetTextColor(200);
$pdf->Rect(125, 80, 40, 10, 'D');
$pdf->Text(125, 79, 'MirrorV');
$pdf->SetDrawColor(0);
$pdf->SetTextColor(0);
//Start Transformation
$pdf->StartTransform();
//mirror vertically with axis of reflection at y-position 90 (bottom side of the rectangle)
$pdf->MirrorV(90);
$pdf->Rect(125, 80, 40, 10, 'D');
$pdf->Text(125, 79, 'MirrorV');
//Stop Transformation
$pdf->StopTransform();

//Point reflection
$pdf->SetDrawColor(200);
$pdf->SetTextColor(200);
$pdf->Rect(50, 110, 40, 10, 'D');
$pdf->Text(50, 109, 'MirrorP');
$pdf->SetDrawColor(0);
$pdf->SetTextColor(0);
//Start Transformation
$pdf->StartTransform();
//point reflection at the lower left point of rectangle
$pdf->MirrorP(50,120);
$pdf->Rect(50, 110, 40, 10, 'D');
$pdf->Text(50, 109, 'MirrorP');
//Stop Transformation
$pdf->StopTransform();

//Mirroring against a straigth line described by a point (120, 120) and an angle -20°
$angle=-20;
$px=120;
$py=120;

//just vor visualisation: the straight line to mirror against

$pdf->SetDrawColor(200);
$pdf->Line($px-1,$py-1,$px+1,$py+1);
$pdf->Line($px-1,$py+1,$px+1,$py-1);
$pdf->StartTransform();
$pdf->Rotate($angle, $px, $py);
$pdf->Line($px-5, $py, $px+60, $py);
$pdf->StopTransform();

$pdf->SetDrawColor(200);
$pdf->SetTextColor(200);
$pdf->Rect(125, 110, 40, 10, 'D');
$pdf->Text(125, 109, 'MirrorL');
$pdf->SetDrawColor(0);
$pdf->SetTextColor(0);
//Start Transformation
$pdf->StartTransform();
//mirror against the straight line
$pdf->MirrorL($angle, $px, $py);
$pdf->Rect(125, 110, 40, 10, 'D');
$pdf->Text(125, 109, 'MirrorL');
//Stop Transformation
$pdf->StopTransform();

// --- END GRAPHIC TRANFORMATIONS TEST ---------------------

$pdf->AddPage();
$pdf->Bookmark('Graphic Functions', 0, 0);

// START GRAPHIC FUNCTION TEST -------------------------------
// Code provided by David Hernandez Sanz

$style = array("width" => 0.5, "cap" => "butt", "join" => "miter", "dash" => "10,20,5,10", "phase" => 10, "color" => array(255, 0, 0));
$style2 = array("width" => 0.5, "cap" => "butt", "join" => "miter", "dash" => 0, "color" => array(255, 0, 0));
$style3 = array("width" => 1, "cap" => "round", "join" => "round", "dash" => "2,10", "color" => array(255, 0, 0));
$style4 = array("L" => 0,
                "T" => array("width" => 0.25, "cap" => "butt", "join" => "miter", "dash" => "20,10", "phase" => 10, "color" => array(100, 100, 255)),
                "R" => array("width" => 0.50, "cap" => "round", "join" => "miter", "dash" => 0, "color" => array(50, 50, 127)),
                "B" => array("width" => 0.75, "cap" => "square", "join" => "miter", "dash" => "30,10,5,10"));
$style5 = array("width" => 0.25, "cap" => "butt", "join" => "miter", "dash" => 0, "color" => array(0, 0, 0));
$style6 = array("width" => 0.5, "cap" => "butt", "join" => "miter", "dash" => "10,10", "color" => array(0, 255, 0));
$style7 = array("width" => 0.5, "cap" => "butt", "join" => "miter", "dash" => 0, "color" => array(200, 200, 0));

// Line
$pdf->Bookmark('Line', 1, 2);
$pdf->Text(5, 7, "Line examples");
$pdf->Line(5, 10, 80, 30, $style);
$pdf->Line(5, 10, 5, 30, $style2);
$pdf->Line(5, 10, 80, 10, $style3);

// Rect
$pdf->Bookmark('Rectangle', 1, 2);
$pdf->Text(100, 7, "Rectangle examples");
$pdf->Rect(100, 10, 40, 20, "DF", $style4, array(220, 220, 200));
$pdf->Rect(145, 10, 40, 20, "D", array("all" => $style3));

// Curve
$pdf->Bookmark('Curve', 1, 32);
$pdf->Text(5, 37, "Curve examples");
$pdf->Curve(5, 40, 30, 55, 70, 45, 60, 75, null, $style6);
$pdf->Curve(80, 40, 70, 75, 150, 45, 100, 75, "F", $style6);
$pdf->Curve(140, 40, 150, 55, 180, 45, 200, 75, "DF", $style6, array(200, 220, 200));

// Circle and ellipse
$pdf->Bookmark('Circle and ellipse', 1, 77);
$pdf->Text(5, 82, "Circle and ellipse examples");
$pdf->SetLineStyle($style5);
$pdf->Circle(25,105,20);
$pdf->Circle(25,105,10, 90, 180, null, $style6);
$pdf->Circle(25,105,10, 270, 360, "F");
$pdf->Circle(25,105,10, 270, 360, "C", $style6);

$pdf->SetLineStyle($style5);
$pdf->Ellipse(100,105,40,20);
$pdf->Ellipse(100,105,20,10, 0, 90, 180, null, $style6);
$pdf->Ellipse(100,105,20,10, 0, 270, 360, "DF", $style6);

$pdf->SetLineStyle($style5);
$pdf->Ellipse(175,105,30,15,45);
$pdf->Ellipse(175,105,15,7.50, 45, 90, 180, null, $style6);
$pdf->Ellipse(175,105,15,7.50, 45, 270, 360, "F", $style6, array(220, 200, 200));

// Polygon
$pdf->Bookmark('Polygon', 1, 127);
$pdf->Text(5, 132, "Polygon examples");
$pdf->SetLineStyle(array("width" => 0.5, "cap" => "butt", "join" => "miter", "dash" => 0, "color" => array(0, 0, 0)));
$pdf->Polygon(array(5,135,45,135,15,165));
$pdf->Polygon(array(60,135,80,135,80,155,70,165,50,155), "DF", array($style6, $style7, $style7, 0, $style6));
$pdf->Polygon(array(120,135,140,135,150,155,110,155), "D", array($style6, 0, $style7, $style6));
$pdf->Polygon(array(160,135,190,155,170,155,200,160,160,165), "DF", array("all" => $style6), array(220, 220, 220));

// Regular polygon
$pdf->Bookmark('Regular polygon', 1, 167);
$pdf->Text(5, 172, "Regular polygon examples");
$pdf->SetLineStyle($style5);
$pdf->RegularPolygon(20, 190, 15, 6, 0, 1, "F");
$pdf->RegularPolygon(55, 190, 15, 6);
$pdf->RegularPolygon(55, 190, 10, 6, 45, 0, "DF", array($style6, 0, $style7, 0, $style7, $style7, $style6));
$pdf->RegularPolygon(90, 190, 15, 3, 0, 1, "DF", array("all" => $style5), array(200, 220, 200), "F", array(255, 200, 200));
$pdf->RegularPolygon(125, 190, 15, 4, 30, 1, null, array("all" => $style5), null, null, $style6);
$pdf->RegularPolygon(160, 190, 15, 10);

// Star polygon
$pdf->Bookmark('Star polygon', 1, 207);
$pdf->Text(5, 212, "Star polygon examples");
$pdf->SetLineStyle($style5);
$pdf->StarPolygon(20, 230, 15, 20, 3, 0, 1, "F");
$pdf->StarPolygon(55, 230, 15, 12, 5);
$pdf->StarPolygon(55, 230, 7, 12, 5, 45, 0, "DF", array($style6, 0, $style7, 0, $style7, $style7, $style6));
$pdf->StarPolygon(90, 230, 15, 20, 6, 0, 1, "DF", array("all" => $style5), array(220, 220, 200), "F", array(255, 200, 200));
$pdf->StarPolygon(125, 230, 15, 5, 2, 30, 1, null, array("all" => $style5), null, null, $style6);
$pdf->StarPolygon(160, 230, 15, 10, 3);
$pdf->StarPolygon(160, 230, 7, 50, 26);

// Rounded rectangle
$pdf->Bookmark('Rounded rectangle', 1, 247);
$pdf->Text(5, 252, "Rounded rectangle examples");
$pdf->SetLineStyle(array("width" => 0.5, "cap" => "butt", "join" => "miter", "dash" => 0, "color" => array(0, 0, 0)));
$pdf->RoundedRect(5, 255, 40, 30, 3.50, "1111", "DF");
$pdf->RoundedRect(50, 255, 40, 30, 6.50, "1000");
$pdf->RoundedRect(95, 255, 40, 30, 10.0, "1111", null, $style6);
$pdf->RoundedRect(140, 255, 40, 30, 8.0, "0101", "DF", $style6, array(200, 200, 200));

// END GRAPHIC FUNCTION TEST -------------------------------


// JAVASCRIPT FORM ------------------------------------------
// Code Provided by Denis Van Nuffelen
$pdf->setPrintHeader(true);
$pdf->AddPage();
$pdf->Bookmark('Javascript Form', 0, 0);
$pdf->setPrintFooter(true);

/*
Caution: the generated PDF works only with Acrobat Reader 5.1.
It is possible to create text fields, combo boxes, check boxes and buttons. Fields are created at the current position and are given a name. This name allows to manipulate them via JavaScript in order to perform some validation for instance.
Upon field creation, an associative array can be passed to set a number of properties, among which:
TextColor (black by default)
FillColor (transparent by default)
BorderColor (transparent by default)
BorderStyle (solid, dashed, beveled, inset or underline; solid by default)
Colors can be chosen in the following list (case sensitive): black white red green blue cyan magenta yellow dkGray gray ltGray or be in the form #RRGGBB.
*/

$pdf->Cell(0,5,'Subscription form',0,1,'C');
$pdf->Ln(10);
$pdf->SetFont('','',12);
//First name
$pdf->Cell(35,5,'First name:');
$pdf->TextField('firstname',50,5,array('BorderColor'=>'ltGray'));
$pdf->Ln(6);
//Last name
$pdf->Cell(35,5,'Last name:');
$pdf->TextField('lastname',50,5,array('BorderColor'=>'ltGray'));
$pdf->Ln(6);
//Gender
$pdf->Cell(35,5,'Gender:');
$pdf->ComboBox('gender',10,5,array('','M','F'),array('BorderColor'=>'ltGray'));
$pdf->Ln(6);
//Adress
$pdf->Cell(35,5,'Address:');
$pdf->TextField('address',60,18,array('multiline'=>true,'BorderColor'=>'ltGray'));
$pdf->Ln(19);
//E-mail
$pdf->Cell(35,5,'E-mail:');
$pdf->TextField('email',50,5,array('BorderColor'=>'ltGray'));
$pdf->Ln(6);
//Newsletter
$pdf->Cell(35,5,'Receive our',0,1);
$pdf->Cell(35,5,'newsletter:');
$pdf->CheckBox('newsletter',5,true);
$pdf->Ln(10);
//Date of the day (determined and formatted by JS)
$pdf->Write(5,'Date: ');
$pdf->TextField('date',30,5);
$pdf->IncludeJS("getField('date').value=util.printd('dd/mm/yyyy',new Date());");
$pdf->Ln();
$pdf->Write(5,'Signature:');
$pdf->Ln(3);
//Button to validate and print
$pdf->SetX(95);
$pdf->Button('print',20,8,'Print','Print()',array('TextColor'=>'yellow','FillColor'=>'#FF5050'));

//Form validation functions
$pdf->IncludeJS("
function CheckField(name,message)
{
    f=getField(name);
    if(f.value=='')
    {
        app.alert(message);
        f.setFocus();
        return false;
    }
    return true;
}

function Print()
{
    //Validation
    if(!CheckField('firstname','First name is mandatory'))
        return;
    if(!CheckField('lastname','Last name is mandatory'))
        return;
    if(!CheckField('gender','Gender is mandatory'))
        return;
    if(!CheckField('address','Address is mandatory'))
        return;
    //Print
    print();
}
");

// END JAVASCRIPT FORM --------------------------------------




//Close and output PDF document
$pdf->Output();

//============================================================+
// END OF FILE                                                 
//============================================================+
?>