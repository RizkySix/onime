<x-bootsrap.doc-main-view title="API Documentation">
  
    <div class="container mt-4">
        <div class="col-lg-10 m-auto">
           <div class="header-content">
                <h2>All Anime By Genre</h2>
                <h6>Memberikan seluruh anime sesuai nama genre.</h6>
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
                <input type="text" class="form-control w-50" readonly value="http://onime.test/api/ver1/genres/genre_name">
            </div>

            <x-bootsrap.note-snipet>
                Genre name bisa anda dapatkan pada response <a href="{{ route('doc.genre') }}">genre list</a>. <br>
                <span style="font-style: italic">Ingat genre_name tidak bersifat Case Sensitive</span>

            </x-bootsrap.note-snipet>

            <div class="example-response">
                <h4>Example Response</h4>
                <p>Berikut adalah contoh response yang dikembalikan dari permintaan ke endpoint diatas.</p>
<x-bootsrap.code-snipet>
{
    "status": true,
    "genre": {
        "genre_name": "Shounen",
        "animes": [
            {
                "anime_name": "Jigoroku",
                "slug": "jigoroku",
                "total_episode": 12,
                "rating": 5.7,
                "released_date": "Spring,2021",
                "studio": "Mappa",
                "author": "Himiko",
                "vip": true,
                "description": "Killing and Echi",
                "genres": "Action, Killing, Scifi, Shounen"
            },
            {
                "anime_name": "Hontoni Souya",
                "slug": "hontoni-souya",
                "total_episode": 24,
                "rating": 4,
                "released_date": "2012",
                "studio": "Mappa",
                "author": "Miku",
                "vip": true,
                "description": "School but Cool",
                "genres": "Drama, Fantasy, Hun, School, Shounen"
            },
            {
                "anime_name": "Baskat Ball",
                "slug": "baskat-ball",
                "total_episode": 12,
                "rating": 0,
                "released_date": "Unknown",
                "studio": "Mappa",
                "author": "Janu",
                "vip": false,
                "description": "Ball and Soccer",
                "genres": "Action, Jump, Shounen, Sport"
            }
        ]
    }
}
</x-bootsrap.code-snipet>              

            </div>

           </div>
           <hr class="text-muted mt-4 mb-4">
           <div class="parameter mb-4">
            <h4>Query Parameters</h4>
            <p>Query parameters yang dapat digunakan dalam permintaan ke endpoint diatas.</p>
            <table class="table table-bordered mt-3">
                <thead>
                    <tr>
                        <th>Parameter</th>
                        <th>Value</th>
                        <th>Description</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="">rating</td>
                        <td class="w-25"><span class="text-muted" style="font-style: italic">String</span> ('true')</td>
                        <td class="">Mengurutkan list data anime berdasarkan rating</td>
                        <td class="">Optional</td>
                    </tr>
                </tbody>
              </table>
       </div>


        </div>
    </div>    

</x-bootsrap.doc-main-view>