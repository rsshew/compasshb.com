<?php namespace CompassHB\Www\Http\Controllers;

use Log;
use CompassHB\Www\Blog;
use CompassHB\Www\Slide;
use CompassHB\Www\Series;
use CompassHB\Www\Sermon;
use CompassHB\Www\Passage;
use CompassHB\Video\Client as VideoClient;
use CompassHB\Photo\Client as PhotoClient;

class PagesController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Homepage.
     */
    public function home()
    {
        $slides = Slide::latest('published_at')->published()->take(2)->get();
        $sermons = Sermon::where('ministry', '=', null)->latest('published_at')->published()->take(4)->get();
        $prevsermon = $sermons->first();
        $nextsermon = Sermon::unpublished()->get();

        $blogs = Blog::latest('published_at')->published()->take(2)->get();
        $videos = Blog::whereNotNull('video')->latest('published_at')->published()->take(2)->get();
        $passage = Passage::latest('published_at')->published()->take(1)->get()->first();

        foreach ($sermons as $sermon) {
            $client = new VideoClient($sermon->video);
            $sermon->othumbnail = $client->getThumbnail();
        }

        foreach ($videos as $video) {
            $client = new VideoClient($video->video);
            $video->othumbnail = $client->getThumbnail();
        }

        $client = new VideoClient($prevsermon->video);
        $prevsermon->othumbnail = $client->getThumbnail(true);

        // Instagram
        $url = 'https://api.instagram.com/v1/users/1363574956/media/recent/?count=4&client_id='.env('INSTAGRAM_CLIENT_ID');
        try {
            $instagrams = file_get_contents($url);
            $instagrams = json_decode($instagrams, true);
        } catch (\Exception $e) {
            Log::warning('Connection refused to api.instagram.com');

            $instagrams['data'] = [];
        }

        $results = new PhotoClient();
        $results = $results->getPhotos(8);

        return view('pages.index', compact(
            'sermons',
            'slides',
            'nextsermon',
            'prevsermon',
            'blogs',
            'videos',
            'passage'
        ))->with('images', $results)
            ->with('instagrams', $instagrams['data'])
            ->with('title', 'Compass HB - Huntington Beach');
    }

    /**
     * Populate the Photos page from Photo Client.
     *
     * @return \Illuminate\View\View
     */
    public function photos()
    {
        $results = new PhotoClient();
        $results = $results->getRecentPhotos();

        return view('pages.photos')
            ->with('title', 'Photography')
            ->with('photos', $results);
    }

    public function pray()
    {
        return view('dashboard.pray.index')
            ->with('title', 'Pray');
    }

    public function whoweare()
    {
        return view('pages.whoweare')->with('title', 'Who We Are');
    }

    public function eightdistinctives()
    {
        return view('pages.eightdistinctives')->with('title', '8 Distinctives');
    }

    public function give()
    {
        return view('pages.give')->with('title', 'Give');
    }

    public function icecreamevangelism()
    {
        return view('pages.icecreamevangelism')->with('title', 'Ice Cream Evangelism');
    }

    public function kids()
    {
        return view('ministries.kids.index')->with('title', 'Kids Ministry');
    }

    public function whatwebelieve()
    {
        return view('pages.whatwebelieve')->with('title', 'What We Believe');
    }

    public function youth()
    {
        return view('ministries.youth.index')->with('title', 'The United');
    }

    public function sundayschool()
    {
        $sermons = Sermon::where('ministry', '=', 'sundayschool')->published()->get();
        $upcoming = Sermon::where('ministry', '=', 'sundayschool')->unpublished()->get();
        $series = Series::where('ministry', '=', 'sundayschool')->get();

        return view('ministries.sundayschool.index', compact('sermons', 'upcoming', 'series'))
            ->with('title', 'Sunday School');
    }

    public function college()
    {
        return view('ministries.college.index')->with('title', 'The Underground');
    }

    public function bunnyrun()
    {
        return view('pages.landing.bunnyrun')->with('title', 'The Bunny Run 5K');
    }

    public function calendar()
    {
        return view('pages.calendar')->with('title', 'Calendar');
    }
}
