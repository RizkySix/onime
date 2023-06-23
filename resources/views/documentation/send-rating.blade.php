<x-bootsrap.doc-main-view title="API Documentation">
  
    <div class="container mt-4">
        <div class="col-lg-10 m-auto">
           <div class="header-content ">
                <h2>Contributing Send Rating Point</h2>
                <h6>Kami sangat mengharapkan dukungan pengguna layanan API kami untuk menggunakan endpoint ini pada website atau aplikasi kalian. <br> Dengan harapan pengguna pada website atau aplikasi kalian dapat berkontribusi untuk memberikan rating terhadap anime-anime yang kami sajikan, Terimakasi.</h6>
           </div>
           <hr class="text-muted mt-4 mb-4">
           <div class="content">
            <div class="endpoint mb-4">
               <div class="d-flex">
                <h4>Api Endpoint </h4> 
                <x-bootsrap.http-request class="bg-danger">
                    PUT
                </x-bootsrap.http-request>
               </div>
                <input type="text" class="form-control w-50" readonly value="http://onime.test/api/ver1/animes/slug/rating">
            </div>
            <div class="example-response">
                <h4>Example Response</h4>
                <p>Berikut adalah contoh response yang dikembalikan dari permintaan ke endpoint diatas misal mengirim 10 point.</p>
<x-bootsrap.code-snipet>
{
    "status": true,
    "message": "Thanks your participation with 10 point"
}
</x-bootsrap.code-snipet>

            </div>
           </div>
           <hr class="text-muted mt-4 mb-4">
           <div class="parameter mb-4">
                <div class="d-flex"><h4>Body </h4> <h5 class="text-muted ms-1 mt-1">( x-www-form-urlencoded )</h5></div>
                <p>Body key yang harus anda kirim pada permintaan endpoint diatas.</p>
                <table class="table table-bordered mt-3">
                    <thead>
                        <tr>
                            <th>Key</th>
                            <th>Value</th>
                            <th>Description</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="">point</td>
                            <td class="w-25"><span class="text-muted" style="font-style: italic">Integer</span> (exm:10)</td>
                            <td class="">Memberikan point untuk meningkatkan rating anime</td>
                            <td class="">Required</td>
                        </tr>
                    </tbody>
                  </table>
           </div>

          <x-bootsrap.note-snipet>
           <span style="font-style: italic">Interval value untuk point yang dapat dikirim adalah <span class="fw-bold">1</span> sampai <span class="fw-bold">10</span>.</span>
          </x-bootsrap.note-snipet>
        </div>
    </div>    

</x-bootsrap.doc-main-view>