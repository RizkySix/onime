<x-bootsrap.doc-main-view title="API Documentation">
  
    <div class="container mt-4">
        <div class="col-lg-10 m-auto">
           <div class="header-content">
                <h2>Show Specific Anime</h2>
                <h6>Menampilkan seluruh data anime berdasarkan slug.</h6>
           </div>
           <hr class="text-muted mt-4 mb-4">
           <div class="content">
            <div class="endpoint mb-4">
               <div class="d-flex">
                <h4>Api Endpoint </h4> 
                <x-bootsrap.http-request>
                    GET
                </x-bootsrap.http-request>
               </div>
                <input type="text" class="form-control w-50" readonly value="http://onime.test/api/ver1/animes/slug">
            </div>

            <x-bootsrap.note-snipet>
               Slug dapat anda dapatkan dari response <a href="{{ route('doc.get-all') }}">get all</a>.
              </x-bootsrap.note-snipet>

            <div class="example-response">
                <h4>Example Response</h4>
                <p>Berikut adalah contoh response yang dikembalikan dari permintaan ke endpoint diatas.</p>
<x-bootsrap.code-snipet>
{
    "status": true,
    "message": "On single anime page",
    "anime": {
        "anime_name": "Jigoroku",
        "slug": "jigoroku",
        "total_episode": 12,
        "rating": 5.7,
        "released_date": "Spring,2021",
        "studio": "Mappa",
        "author": "Himiko",
        "vip": true,
        "description": "Killing and Echi",
        "genre": "Action, Killing, Scifi, Shounen",
        "anime_videos": [
            {
                "title": "Vinland Saga Eps 1.mp4",
                "resolution": "480p",
                "duration": "25 minute",
                "video_format": "mp4",
                "video_url": "http://onime.test/storage/F-Jigoroku/Vinland Saga Eps 1.mp4",
                "short_clip": {
                    "short_name": "clip-Vinland Saga Eps 1.mp4",
                    "duration": "10 second",
                    "short_url": "http://onime.test/storage/short_anime_clip/short-Jigoroku/clip-Vinland Saga Eps 1.mp4"
                }
            }
        ]
},
    "related_animes": [
        {
            "anime_name": "One Puch Man",
            "slug": "one-puch-man",
            "total_episode": 24,
            "rating": 7.7,
            "released_date": "2012",
            "studio": "Mappa",
            "author": "Shirahosi",
            "vip": false,
            "description": "Shounen and Diff",
            "genre": "Demon, Hun, Killing, Scifi"
        }
    ]
}
</x-bootsrap.code-snipet>              

            </div>

             <x-bootsrap.note-snipet class="mt-4">
                Anime yang berhubungan berdasarkan genre sejenis juga akan diberikan pada response. Short-clip juga tersedia untuk masing-masing video, mungkin anda ingin menggunakannya sebagai anime preview.
              </x-bootsrap.note-snipet>

           </div>
           <hr class="text-muted mt-4 mb-4">
        
        </div>
    </div>    

</x-bootsrap.doc-main-view>