<?php

namespace App\Events;

use App\Models\Gaji;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GajiCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $gaji;
    /**
     * Create a new event instance.
     */
    public function __construct(Gaji $gaji)
    {
        //
        $this->gaji = $gaji;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn()
    {
        return new Channel('gajis');
    }

    public function broadcastAs()
    {
        return 'gaji.created';
    }

    public function broadcastWith()
    {
        return [
            'gaji' => [
                'id' => $this->gaji->id,
                'karyawan_id' => $this->gaji->karyawan_id,
                'nama_karyawan' => $this->gaji->karyawan->nama,
                'tanggal_gaji' => $this->gaji->tanggal_gaji->format('Y-m-d'),
                'gaji_pokok' => $this->gaji->gaji_pokok,
                'tunjangan_kehadiran' => $this->gaji->tunjangan_kehadiran,
                'lembur' => $this->gaji->lembur,
                'potongan' => $this->gaji->potongan,
                'gaji_bersih' => $this->gaji->gaji_bersih,
                'validated' => $this->gaji->validated,
            ],
        ];
    }
}
