<?php

namespace App\Observers;

use App\Models\Ticket;

class TicketObserver
{
    /**
     * Handle the Ticket "saved" event.
     *
     * @param  \App\Models\Ticket  $ticket
     * @return void
     */
    public function saved(Ticket $ticket)
    {
        if ($ticket->epic) {
            // $ticket->epic->recalculateProgress();
            $ticket->epic->recalculateDates();
        }
    }

    /**
     * Handle the Ticket "deleted" event.
     *
     * @param  \App\Models\Ticket  $ticket
     * @return void
     */
    public function deleted(Ticket $ticket)
    {
        if ($ticket->epic) {
            // $ticket->epic->recalculateProgress();
            $ticket->epic->recalculateDates();
        }
    }
}
