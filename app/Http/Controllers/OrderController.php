<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Event;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;


class OrderController extends Controller
{
    private function generateUniqueBarcode()
    {
        do {
            $barcode = str_pad(mt_rand(0, 9999999999), 10, '0', STR_PAD_LEFT);
        } while (Ticket::where('barcode', $barcode)->exists());
        return $barcode;
    }



    public function showBookingForm()
    {
        $events = Event::all();
        $orders = Order::all();
        return view('orders.book', compact('events', 'orders'));
    }



    public function bookOrder(Request $request)
    {
        $event_id = $request->event_id;
        $event_date = $request->event_date;
        $ticket_adult_price = $request->ticket_adult_price;
        $ticket_adult_quantity = $request->ticket_adult_quantity;
        $ticket_kid_price = $request->ticket_kid_price;
        $ticket_kid_quantity = $request->ticket_kid_quantity;

        $apiResponse = $this->sendBookingRequest($event_id, $event_date, $ticket_adult_price, $ticket_adult_quantity, $ticket_kid_price, $ticket_kid_quantity);

        if (isset($apiResponse['message'])) {
            $barcode = $apiResponse['barcode'];
            $approvalResponse = $this->sendApprovalRequest($barcode);

            if (isset($approvalResponse['message'])) {
                $this->saveOrderToDB($barcode, $event_id, $event_date, $ticket_adult_price, $ticket_adult_quantity, $ticket_kid_price, $ticket_kid_quantity);
                return response()->json(['message' => 'Order successfully saved to database'], 200);
            } else {
                return response()->json(['error' => 'Order approval failed: ' . $approvalResponse['error']], 400);
            }
        } else {
            return response()->json(['error' => 'Booking failed: ' . $apiResponse['error']], 400);
        }
    }




    private function sendBookingRequest($event_id, $event_date, $ticket_adult_price, $ticket_adult_quantity, $ticket_kid_price, $ticket_kid_quantity)
    {
        $attempts = 0;
        $maxAttempts = 5;

        $barcode = $this->generateUniqueBarcode();

        Http::fake([
            'https://api.site.com/book' => function ($request) use ($barcode) {
                if ($request['barcode'] === $barcode) {
                    return Http::response(['error' => 'barcode already exists'], 400);
                }
                return Http::response(['message' => 'Order successfully booked', 'barcode' => $barcode], 200);
            },
        ]);

        do {
            $response = Http::post('https://api.site.com/book', [
                'event_id' => $event_id,
                'event_date' => $event_date,
                'ticket_adult_price' => $ticket_adult_price,
                'ticket_adult_quantity' => $ticket_adult_quantity,
                'ticket_kid_price' => $ticket_kid_price,
                'ticket_kid_quantity' => $ticket_kid_quantity,
                'barcode' => $barcode,
            ]);

            if ($response->successful() && $response->json('message') === 'Order successfully booked') {
                return ['message' => 'Order successfully booked', 'barcode' => $barcode];
            }

            if ($response->json('error') === 'barcode already exists') {
                $barcode = $this->generateUniqueBarcode();
                $attempts++;
            } else {
                return ['error' => $response->json('error')];
            }

        } while ($attempts < $maxAttempts);

        return ['error' => 'Failed to book order after multiple attempts'];
    }

    private function sendApprovalRequest($barcode)
    {
        Http::fake([
            'https://api.site.com/approve' => function ($request) {
                $responses = [
                    ['message' => 'Order successfully approved'],
                    ['error' => 'event cancelled'],
                    ['error' => 'no tickets'],
                    ['error' => 'no seats'],
                    ['error' => 'fan removed'],
                ];
                return Http::response($responses[array_rand($responses)], 200);
            },
        ]);

        $response = Http::post('https://api.site.com/approve', ['barcode' => $barcode]);

        if ($response->successful() && $response->json('message') === 'Order successfully approved') {
            return ['message' => 'Order successfully approved'];
        }

        return ['error' => $response->json('error') ?? 'Unknown error'];
    }



    public function saveOrderToDB($barcode, $event_id, $event_date, $ticket_adult_price, $ticket_adult_quantity, $ticket_kid_price, $ticket_kid_quantity)
    {
        $order = new Order();
        $order->barcode = $barcode;
        $order->event_id = $event_id;
        $order->event_date = $event_date;
        $order->ticket_adult_price = $ticket_adult_price;
        $order->ticket_adult_quantity = $ticket_adult_quantity;
        $order->ticket_kid_price = $ticket_kid_price;
        $order->ticket_kid_quantity = $ticket_kid_quantity;
        $order->equal_price = ($ticket_adult_price * $ticket_adult_quantity) +
            ($ticket_kid_price * $ticket_kid_quantity);
        $order->save();

        $this->generateTicketsForOrder($order);
    }

    private function generateTicketsForOrder($order)
    {
        for ($i = 0; $i < $order->ticket_adult_quantity; $i++) {
            Ticket::create([
                'order_id' => $order->id,
                'ticket_type' => 'adult',
                'ticket_price' => $order->ticket_adult_price,
                'ticket_quantity' => 1,
                'barcode' => $this->generateUniqueBarcode()
            ]);
        }

        for ($i = 0; $i < $order->ticket_kid_quantity; $i++) {
            Ticket::create([
                'order_id' => $order->id,
                'ticket_type' => 'kid',
                'ticket_price' => $order->ticket_kid_price,
                'ticket_quantity' => 1,
                'barcode' => $this->generateUniqueBarcode()
            ]);
        }
    }
}
