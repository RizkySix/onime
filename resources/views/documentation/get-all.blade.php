<x-bootsrap.doc-main-view title="API Documentation">
  
    <div class="container mt-4">
        <div class="col-lg-10 m-auto">
           <div class="header-content">
                <h2>Get Free Anime</h2>
                <h6>Memberikan seluruh data anime-anime gratis dari server kami.</h6>
           </div>
           <hr class="text-muted mt-4 mb-4">
           <div class="content">
            <div class="endpoint mb-4">
                <h4>Api Endpoint</h4>
                <input type="text" class="form-control w-50" readonly value="http://onime.test/api/ver1/animes">
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
                                "anime_name": "Baskat Ball",
                                "slug": "baskat-ball",
                                "total_episode": 12,
                                "rating": 0,
                                "released_date": "Unknown",
                                "studio": "Mappa",
                                "author": "Janu",
                                "vip": false,
                                "description": "Ball and Soccer",
                                "genre": "Action, Jump, Shounen, Sport"
                            },
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
           </div>
           <hr class="text-muted mt-4 mb-4">
           <div class="parameter">
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