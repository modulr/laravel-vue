<?php

namespace App\Http\Controllers\Events;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Events\Event;
use App\Models\Events\EventImage;
use App\Http\Helpers\Upload;


class EventController extends Controller
{
    public function index()
    {
        return view('events.events');
    }

    public function all()
    {
        $events = Event::with(['owner', 'images' => function ($query) {
                            $query->orderBy('order', 'asc');
                        }])
                        ->orderBy('id', 'desc')
                        ->paginate(20);
        return $events;
    }

    // public function byOwner()
    // {
    //     return Event::with('owner', 'images')
    //                 ->where('owner_id', Auth::id())
    //                 ->orderBy('id', 'desc')
    //                 ->paginate(20);
    // }

    public function show($id)
    {
        return Event::with(['owner', 'images' => function ($query) {
                            $query->orderBy('order', 'asc');
                          }])->find($id);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string',
        ]);

        $event = Event::create([
            'name' => $request->name,
            'description' => $request->description,
            'place' => $request->place,
            'date' => $request->date,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'content' => $request->content,
            'price' => $request->price,
            'attending_limit' => $request->attending_limit,
            'owner_id' => Auth::id(),
        ]);

        if (count($request->images)) {
            $upload = new Upload();
            foreach ($request->images as $key => $value) {
                if (isset($value['path'])) {
                    $upload->move($value['path'], 'events/'.$event->id.'/images')
                                    ->resize(800,500)->thumbnail(360,130)
                                    ->getData();

                    EventImage::create([
                        'basename' => $value['basename'],
                        'order' => $key,
                        'event_id' => $event->id
                    ]);
                }
            }
        }

        return Event::with(['owner', 'images' => function ($query) {
                            $query->orderBy('order', 'asc');
                        }])->find($event->id);
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name' => 'required|string',
        ]);

        $event = Event::with('owner', 'images')->find($id);
        $event->name = $request->name;
        $event->description = $request->description;
        $event->place = $request->place;
        $event->date = $request->date;
        $event->start_time = $request->start_time;
        $event->end_time = $request->end_time;
        $event->content = $request->content;
        $event->price = $request->price;
        $event->attending_limit = $request->attending_limit;
        $event->save();

        return $event;
    }

    public function destroy($id)
    {
        return Event::destroy($id);
    }


    public function uploadImageTemp(Request $request)
    {
        $this->validate($request, [
            'file' => 'required|max:2000000',
        ]);
        $upload = new Upload();
        $uploadData = $upload->uploadTemp($request->file)->getData();
        return $uploadData;
    }

    public function uploadImage(Request $request)
    {
        $this->validate($request, [
            'file' => 'required|max:2000000',
        ]);
        $upload = new Upload();
        $data = $upload->upload($request->file, 'events/'.$request->id.'/images')
                        ->resize(800,500)->thumbnail(360,130)
                        ->getData();

        $maxOrder = EventImage::where('event_id', $request->id)->max('order');
        $maxOrder ++;

        return EventImage::create([
            'basename' => $data['basename'],
            'order' => $maxOrder,
            'event_id' => $request->id
        ]);
    }

    public function sortImage(Request $request, $eventId)
    {
        foreach ($request->images as $key => $v) {
            EventImage::where('id', $v['id'])
                        ->where('event_id', $eventId)
                        ->update(['order' => $key]);
        }

        return EventImage::where('event_id', $eventId)->orderBy('order', 'asc')->get();
    }

    public function destroyImage($id)
    {
        return EventImage::destroy($id);
    }
}
