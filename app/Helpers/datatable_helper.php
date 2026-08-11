<?php

if (!function_exists('datatable_lang_id')) {
   function datatable_lang_id(): array
   {
      return [
         'search' => 'Cari:',
         'lengthMenu' => 'Tampilkan _MENU_ data',
         'info' => 'Menampilkan _START_ - _END_ dari _TOTAL_ data',
         'infoEmpty' => 'Menampilkan 0 - 0 dari 0 data',
         'infoFiltered' => '(disaring dari _MAX_ data)',
         'zeroRecords' => 'Data tidak ditemukan',
         'emptyTable' => 'Data tidak tersedia',
         'paginate' => [
            'first' => 'Pertama',
            'last' => 'Terakhir',
            'next' => 'Selanjutnya',
            'previous' => 'Sebelumnya'
         ]
      ];
   }
}
