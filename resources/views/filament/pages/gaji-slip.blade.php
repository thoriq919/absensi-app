<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Slip Gaji -->
        @if($showSlip)
            <div id="slip-gaji" class="bg-white p-8 max-w-4xl mx-auto print:shadow-none">
                <!-- Header -->
                <div class="border-2 border-black p-6">
                    <h1 class="text-2xl font-bold mb-6">
                        Slip Gaji {{ \Carbon\Carbon::parse($tanggal_gaji)->translatedFormat('F Y')}}
                    </h1>
                    <br>
                    <!-- Info Karyawan -->
                    <div class="grid grid-cols-2 gap-4 mb-6">
                        <div class="flex">
                            <span class="font-semibold w-32">Nama</span>
                            <span>: {{ $karyawan->nama }}</span>
                        </div>
                        <div class="flex">
                            <span class="font-semibold w-32">No.Telp</span>
                            <span>: {{ $karyawan->no_telp}}</span>
                        </div>
                        <div class="flex">
                            <span class="font-semibold w-32">Gaji Pokok</span>
                            <span>: Rp {{ number_format($gaji_pokok, 0, ',', '.') }}</span>
                        </div>
                    </div>
                    
                    <!-- Tabel Kehadiran -->
                    <br>
                    <table class="w-full border-collapse border border-black mb-6">
                        <thead>
                            <tr class="bg-gray-100">
                                <th class="border border-black p-3 text-center font-semibold">Hadir</th>
                                <th class="border border-black p-3 text-center font-semibold">Izin</th>
                                <th class="border border-black p-3 text-center font-semibold">Sakit</th>
                                <th class="border border-black p-3 text-center font-semibold">Alpha</th>
                                <th class="border border-black p-3 text-center font-semibold">Bonus</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="border border-black p-3 text-center">{{ $hadir }}</td>
                                <td class="border border-black p-3 text-center">{{ $izin }}</td>
                                <td class="border border-black p-3 text-center">{{ $sakit }}</td>
                                <td class="border border-black p-3 text-center">{{ $alpha }}</td>
                                <td class="border border-black p-3 text-center">Rp {{ number_format($bonus, 0, ',', '.') }}</td>
                            </tr>
                        </tbody>
                    </table>
                    
                    <!-- Detail Gaji -->
                    <br>
                    <div class="grid grid-cols-2 gap-4 mb-6 text-sm">
                        <div>
                            <div class="space-y-1">
                                <div class="flex justify-between">
                                    <div class="flex">
                                        <span class="inline-block w-20">Potongan</span>
                                        <span>:</span>
                                    </div>
                                    <span>Rp {{ number_format($potongan, 0, ',', '.') }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <div class="flex">
                                        <span class="inline-block w-20">Lembur</span>
                                        <span>:</span>
                                    </div>
                                    <span>Rp {{ number_format($lembur, 0, ',', '.') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <br>
                    
                    <!-- Garis Pemisah -->
                    <hr class="border-t-2 border-black my-6">
                    
                    <!-- Total Gaji -->
                    <br>
                    <div class="text-right">
                        <span class="text-xl font-bold">
                            Total Gaji: 
                            <span class="text-green-600">
                                Rp {{ number_format($gaji_bersih, 0, ',', '.') }}
                            </span>
                        </span>
                    </div>
                </div>
                
                <!-- Tombol Aksi -->
                <div class="mt-6 flex justify-between no-print">                    
                    <x-filament::button 
                        onclick="window.print()" 
                        color="success"
                    >
                        Cetak Slip Gaji
                    </x-filament::button>
                </div>
            </div>
        @endif
    </div>
    
    <!-- CSS untuk print -->
    <style>
        /* Hide action buttons when printing */
        .no-print {
            display: block;
        }
        
        @media print {
            /* Hide elements that shouldn't be printed */
            .no-print,
            .print\\:hidden {
                display: none !important;
                visibility: hidden !important;
            }
            
            /* Show only the slip content */
            body * {
                visibility: hidden;
            }
            
            #slip-gaji, 
            #slip-gaji * {
                visibility: visible;
            }
            
            /* Hide the action buttons container completely */
            #slip-gaji .no-print,
            #slip-gaji .no-print * {
                visibility: hidden !important;
                display: none !important;
            }
            
            /* Position slip for print */
            #slip-gaji {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
            }
            
            /* Remove shadows for print */
            .print\\:shadow-none {
                box-shadow: none !important;
            }
        }
        
        /* Page settings */
        @page {
            size: A4;
            margin: 2cm;
        }
        
        /* Dark mode adjustments for print */
        @media print {
            .bg-gray-100 {
                background-color: #f3f4f6 !important;
                -webkit-print-color-adjust: exact;
                color-adjust: exact;
            }
            
            /* Ensure black text on white background for print */
            * {
                color: black !important;
                background: white !important;
            }
            
            /* Keep table styling */
            .bg-gray-100 {
                background-color: #f3f4f6 !important;
            }
            
            /* Keep green color for total */
            .text-green-600 {
                color: #059669 !important;
            }
        }
    </style>
</x-filament-panels::page>