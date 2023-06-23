<x-bootsrap.doc-main-view title="API Documentation">
  
    <div class="container mt-4">
        <div class="col-lg-10 m-auto">
           <div class="header-content">
                <h2>Get VIP Anime</h2>
                <h6>Memberikan seluruh data VIP anime dari server kami.</h6>
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
                <input type="text" class="form-control w-50" readonly value="http://onime.test/api/ver1/animes-vip">
            </div>
            <div class="example-response">
                <h4>Example Response</h4>
                <p>Berikut adalah contoh response yang dikembalikan dari permintaan ke endpoint diatas.</p>
<x-bootsrap.code-snipet>
{
    "status": true,
    "total_result_found": 2,
    "paginate": {
        "result_limit": 100,
        "page": 10,
        "current_page": 1,
        "data_per_page": 10
    },
    "animes": [
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
            "genre": "Drama, Fantasy, Hun, School, Shounen"
        },
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
            "genre": "Action, Killing, Scifi, Shounen"
        }
    ]
}
</x-bootsrap.code-snipet>

            </div>

            <x-bootsrap.note-snipet class="mt-4">
                <span class="" style="font-style: italic"><span class="fw-bold">Authentication Token</span> yang anda kirimkan pada http headers saat melakukan permintaan harus memiliki <span class="fw-bold">VIP ability.</span></span><br>
                <span>Anda dapat mendpatkan VIP token dengan berlangganan <a href="{{ route('pricing.index') }}">Pricing</a>.</span>
              </x-bootsrap.note-snipet>

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
                            <td class="">find_anime</td>
                            <td class="w-25"><span class="text-muted" style="font-style: italic">String</span> (exm:'Kimetsu')</td>
                            <td class="">Mencari nama anime-anime yang sesuai berdasarkan parameter yang diberikan</td>
                            <td class="">Optional</td>
                        </tr>
                        <tr>
                            <td class="">rating</td>
                            <td class="w-25"><span class="text-muted" style="font-style: italic">String</span> ('true')</td>
                            <td class="">Mengurutkan list data anime berdasarkan rating</td>
                            <td class="">Optional</td>
                        </tr>
                        <tr>
                            <td class="">page</td>
                            <td class="w-25"><span class="text-muted" style="font-style: italic">Integer</span> (exm:2)</td>
                            <td class="">Berpindah ke halaman berikutnya dari list data anime</td>
                            <td class="">Optional</td>
                        </tr>
                    </tbody>
                  </table>
           </div>

          <x-bootsrap.note-snipet>
            Response akan mengembalikan list json kosong jika value find_anime tidak ada yang sesuai dengan data pada server. Maksimal page yang disajikan dari list data adalah 10 page dengan 10 data per page, silahkan berikan value yang spesifik untuk mendapatkan response yang diinginkan.
          </x-bootsrap.note-snipet>
        </div>
    </div>    

</x-bootsrap.doc-main-view>