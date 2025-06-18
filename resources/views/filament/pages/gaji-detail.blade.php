<x-filament-panels::page>
    <div class="w-full overflow-x-auto">
        <div id="calendar" class="min-w-full"></div>
    </div>
</x-filament-panels::page>

@push('scripts')
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css' rel='stylesheet' />
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js'></script>

    <script>
        const selectedDate = @json(\Carbon\Carbon::parse($gaji->tanggal_gaji)->toDateString());

        document.addEventListener('DOMContentLoaded', function () {
            const calendarEl = document.getElementById('calendar');
            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                initialDate: selectedDate,
                events: @json($events),
                height: 'auto',
                aspectRatio: 1.35, // Menentukan rasio aspek kalender
                
                headerToolbar: {
                    left: '',      
                    center: 'title',
                    right: '',     
                },

                navLinks: false,
                editable: false,
                selectable: false,
                dateClick: null,
                eventClick: null,
                eventMouseEnter: null,
                
                // Memastikan kalender responsive
                windowResizeDelay: 100,
                handleWindowResize: true,
            });
            
            calendar.render();
            
            // Force render ulang setelah DOM siap
            setTimeout(() => {
                calendar.updateSize();
            }, 100);
        });
    </script>
@endpush

@push('styles')
    <style>
        /* Reset CSS untuk kalender */
        #calendar {
            max-width: 100%;
            margin: 0 auto;
        }

        /* Memastikan container kalender tidak overflow */
        .fc {
            width: 100% !important;
        }

        /* Ukuran angka tanggal */
        .fc-daygrid-day-number {
            font-size: 0.75rem;
        }

        /* Ukuran teks event */
        .fc-event-title {
            font-size: 0.75rem;
        }

        .fc-col-header-cell-cushion {
            font-size: 0.8rem;
        }

        .fc-daygrid-day-frame {
            padding: 2px;
        }

        /* Fix untuk responsive */
        .fc-header-toolbar {
            flex-wrap: wrap;
        }

        /* Memastikan sel kalender tidak terpotong */
        .fc-daygrid-day {
            min-height: 80px;
        }

        /* Override Filament container jika diperlukan */
        .fi-page {
            overflow-x: visible !important;
        }

        /* Responsive untuk mobile */
        @media (max-width: 768px) {
            .fc-daygrid-day {
                min-height: 60px;
            }
            
            .fc-daygrid-day-number {
                font-size: 0.7rem;
            }
            
            .fc-event-title {
                font-size: 0.7rem;
            }
        }
    </style>
@endpush