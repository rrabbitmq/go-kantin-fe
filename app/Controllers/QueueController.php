<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Antrian;
use App\Models\Keranjang;

final class QueueController extends Controller
{
    public function index(): void
    {
        $this->requireRole('pembeli');

        $slots = Antrian::slots();
        $currentSlot = Antrian::current();
        $queueThreshold = 30;
        $currentCount = $currentSlot ? (int) $currentSlot['queue_count'] : 0;

        $this->render('pembeli/queue', [
            'pageTitle' => 'Antrian Kantin — Go Kantin',
            'activeNav' => 'status',
            'currentUser' => $this->currentUserForLayout(),
            'cartCount' => Keranjang::countForUser((int) Auth::id()),
            'slots' => $slots,
            'currentSlot' => $currentSlot,
            'queueThreshold' => $queueThreshold,
            'currentCount' => $currentCount,
            'currentLabel' => gk_queue_label($currentCount, $queueThreshold),
        ]);
    }
}
