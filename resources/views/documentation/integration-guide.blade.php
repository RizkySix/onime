<x-bootsrap.doc-main-view title="API Documentation">


<div class="container mt-4">
    <div class="col-lg-10 m-auto">
       <div class="header-content">
            <h2>Getting Started</h2>
            <h6>Persiapan sebelum mulai menggunakan API</h6>
       </div>
       <hr class="text-muted mt-4 mb-4">
       <div class="content">
        <div class="regis">
            <h4>1. Sign Up ke Onime</h4>
            <p>Sebelum memulai anda harus memiliki akun ONIME untuk dapat mengenerate Authentication Token sebagai persyaratan menggunakan API kami. Untuk register anda dapat mengunjungi url <a href="{{ route('register') }}">ini</a>.</p>
        </div>
        <div class="get-key">
            <h4>2. Generate Token</h4>
            <p>Anda dapat generate Authentication Token pada halaman <a href="{{ route('dashboard') }}">dashboard</a> setelah login. Token ini akan selalu dikirim pada <span class="fw-bold">headers authorization</span> sebagai bearer token setiap kali melakukan request API.</p>
        </div>
       </div>
       <hr class="text-muted mt-4 mb-4">
       <div class="using">
        <div class="content">
            <h4>Bagaimana ini Bekerja</h4>
            <p>Berikut parameter yang harus dikirim pada HTTP headers.</p>
            <table class="table table-bordered mt-3">
                <tbody>
                    <tr>
                        <td class="w-50">Accept</td>
                        <td class="w-50">application/json</td>
                      </tr>
                    <tr>
                        <td class="w-50">Authorization</td>
                        <td class="w-50">bearer 5|kYs4XaskkkVlaU5LZpBxgpaBVrcTEzCFgEsiwRFl</td>
                    </tr>
                </tbody>
              </table>
        </div>
       </div>
    </div>
</div>    

</x-bootsrap.doc-main-view>