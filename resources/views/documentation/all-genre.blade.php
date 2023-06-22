<x-bootsrap.doc-main-view title="API Documentation">
  
    <div class="container mt-4">
        <div class="col-lg-10 m-auto">
           <div class="header-content">
                <h2>Get All Genre</h2>
                <h6>Memberikan data genre yang tersedia.</h6>
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
                <input type="text" class="form-control w-50" readonly value="http://onime.test/api/ver1/genres">
            </div>

            <div class="example-response">
                <h4>Example Response</h4>
                <p>Berikut adalah contoh response yang dikembalikan dari permintaan ke endpoint diatas.</p>
<x-bootsrap.code-snipet>
{
    "status": true,
    "message": "All anime genres",
    "genres": [
        {
            "genre_name": "Shounen",
            "anime_result": 3
        },
        {
            "genre_name": "Hun",
            "anime_result": 2
        },
        {
            "genre_name": "Killing",
            "anime_result": 2
        },
        {
            "genre_name": "Scifi",
            "anime_result": 2
        },
        {
            "genre_name": "Action",
            "anime_result": 2
        },
        {
            "genre_name": "Demon",
            "anime_result": 1
        },
        {
            "genre_name": "Sport",
            "anime_result": 1
        },
        {
            "genre_name": "Jump",
            "anime_result": 1
        },
        {
            "genre_name": "School",
            "anime_result": 1
        },
        {
            "genre_name": "Drama",
            "anime_result": 1
        },
        {
            "genre_name": "Fantasy",
            "anime_result": 1
        }
    ]
}
</x-bootsrap.code-snipet>              

            </div>

           </div>
           <hr class="text-muted mt-4 mb-4">
        
        </div>
    </div>    

</x-bootsrap.doc-main-view>