<?php



$page = '';
if (isset($_GET['page'])){
  $page = $_GET['page'];
}

switch ($page){

  // Mahasiswa 
  case 'booking':
    $page = "include('content/mahasiswa/booking.php');";
    break;

  case 'myBooking':
    $page = "include('content/mahasiswa/myBooking.php');";
    break;


  // Admin
  case 'dataRuangan':
    $page = "include('content/admin/dataRuangan.php');";
    break;

  case 'dataJadwal':
    $page = "include('content/admin/dataJadwal.php');";
    break;

  case 'kelolaBooking':
    $page = "include('content/admin/kelolaBooking.php');";
    break;
  case 'kelolaBooking1':
    $page = "include('content/mahasiswa/bookingcoba.php');";
    break;

  case 'tambahDataRuangan':
    $page = "include('content/admin/tambahDataRuangan.php');";
    break;

  case 'tambahDataJadwal':
    $page = "include('content/admin/tambahDataJadwal.php');";
    break;

  case 'laporan':
    $page = "include('content/admin/laporan.php');";
    break;


  // Keduanya
  default:
  $page = "include('content/dashboard.php');";
  break;

}

$main = $page;

?>