<x-bootsrap.doc-main-view title="API Documentation">
  
    <div class="container mt-4">
        <div class="col-lg-10 m-auto">
           <div class="header-content">
                <h2>Anime List</h2>
                <h6>Daftar anime berdasarkan huruf depan nama anime.</h6>
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
                <input type="text" class="form-control w-50" readonly value="http://onime.test/api/ver1/anime-list">
            </div>
            <div class="example-response">
                <h4>Example Response</h4>
                <p>Berikut adalah contoh response yang dikembalikan dari permintaan ke endpoint diatas.</p>
<x-bootsrap.code-snipet>
{
    "status": true,
    "message": "Find anime list by send ABCD or so on",
    "animes": [
        {
            "anime_name": "One Puch Man",
            "slug": "one-puch-man"
        }
    ]
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
                            <td class="">list</td>
                            <td class="w-25"><span class="text-muted" style="font-style: italic">String</span> (exm:'o')</td>
                            <td class="">Menampilkan daftar nama anime yang huruf awalnya sesuai dengan karakter pada list</td>
                            <td class="">Required</td>
                        </tr>
                    </tbody>
                  </table>
           </div>

          <x-bootsrap.note-snipet>
           Jika anda tidak memberikan parameter list atau value yang diberikan tidak satu karakter, maka response akan mengembalikan daftar anime yang dipublish dalam 30 hari terakhir.
          </x-bootsrap.note-snipet>
        </div>
    </div>    

</x-bootsrap.doc-main-view>