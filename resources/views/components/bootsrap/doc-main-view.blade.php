<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ $title }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <script src="https://code.jquery.com/jquery-3.7.0.js" integrity="sha256-JlqSTELeR4TLqP0OG9dxM7yDPqX1ox/HfgiSLBj8+kM=" crossorigin="anonymous"></script>
    <style>
        body {
            background-image: url('/asset-img/soft-bg.jpg');
            background-repeat: no-repeat;
            background-size: cover;
        }

        /* scrollbar */
        /* width */
        ::-webkit-scrollbar {
        width: 10px;
        }

        /* Track */
        ::-webkit-scrollbar-track {
        background: #f1f1f1; 
        }
        
        /* Handle */
        ::-webkit-scrollbar-thumb {
        background: #e7d9d9; 
        }

        /* Handle on hover */
        ::-webkit-scrollbar-thumb:hover {
        background: #dbafaf; 
        }


        /* content */

        .left-container{
            position: relative;
        }
        .sidebar-left{
            
            overflow-x: hidden;
            height: 100vh;
            position: sticky;
            z-index: 1;
            top: 0;
            left: 0;

        }
        .api-intergration:hover{
            color: gray;
        }
        .active-url{
           background-color: rgb(224, 222, 220);
           border-radius: 1rem 1rem 1rem 1rem;
        }
    </style>
</head>
<body>
    <x-bootsrap.navbar>
    </x-bootsrap.navbar>

    <div class="left-container d-flex">
        <div class="sidebar-left col-lg-2 bg-white">
            <div class="container text-left mt-4">
            <div class="w-75 m-auto">
                <div class="introduction">
                    <span class="fw-bold h5">Introduction</span><br>
                    <a href="{{ route('doc.guide') }}" class="{{ Request::route()->getName() == 'doc.guide' ? 'active-url' : '' }} text-decoration-none">Getting Started</a>
                  </div>
                  <hr style="margin-top:30px;">
                  <div class="free-anime">
                    <span class="fw-bold h5">Free Anime</span><br>
                    <span id="title-free-api" class="api-intergration" style="cursor: pointer">API Intergration</span>
                    <div class="free-api-list">
                       <a href="{{ route('doc.get-all') }}" class="{{ Request::route()->getName() == 'doc.get-all' ? 'active-url' : '' }} text-decoration-none">&#8594 Get All Anime</a> <br>
                       <a href="{{ route('doc.show') }}" class="{{ Request::route()->getName() == 'doc.show' ? 'active-url' : '' }} text-decoration-none">&#8594 Show Specific Anime</a>
                    </div>
                  </div>
                  <hr style="margin-top:30px;">
                  <div class="genres">
                    <span class="fw-bold h5">Genre</span><br>
                    <span id="title-genre-api" class="api-intergration" style="cursor: pointer">API Intergration</span>
                    <div class="genre-list">
                       <a href="{{ route('doc.genre') }}" class="{{ Request::route()->getName() == 'doc.genre' ? 'active-url' : '' }} text-decoration-none">&#8594 Genre List</a> <br>
                       <a href="{{ route('doc.anime-genre') }}" class="{{ Request::route()->getName() == 'doc.anime-genre' ? 'active-url' : '' }} text-decoration-none">&#8594 Anime By Genre</a>
                    </div>
                  </div>
                  <hr style="margin-top:30px;">
                  <div class="alphabet-list">
                    <span class="fw-bold h5">Anime List</span><br>
                    <span id="title-anime-list" class="api-intergration" style="cursor: pointer">API Intergration</span>
                    <div class="anime-list">
                       <a href="{{ route('doc.anime-list') }}" class="{{ Request::route()->getName() == 'doc.anime-list' ? 'active-url' : '' }} text-decoration-none">&#8594 List By Alphabet</a> <br>
                    </div>
                  </div>
                  <hr style="margin-top:30px;">
                  <div class="anime-vip">
                    <span class="fw-bold h5">Anime VIP</span><br>
                    <span id="title-anime-vip" class="api-intergration" style="cursor: pointer">API Intergration</span>
                    <div class="anime-vip-list">
                       <a href="{{ route('doc.vip-anime') }}" class="{{ Request::route()->getName() == 'doc.vip-anime' ? 'active-url' : '' }} text-decoration-none">&#8594 Get All Anime</a> <br>
                    </div>
                  </div>
                  <hr style="margin-top:30px;">
                  <div class="anime-rating-point">
                    <span class="fw-bold h5">Send Rating</span><br>
                    <span id="title-anime-rating-point" class="api-intergration" style="cursor: pointer">API Intergration</span>
                    <div class="send-rating">
                       <a href="{{ route('doc.send-rating') }}" class="{{ Request::route()->getName() == 'doc.send-rating' ? 'active-url' : '' }} text-decoration-none">&#8594 Contributing Rating</a> <br>
                    </div>
                  </div>
                  <hr style="margin-top:30px;">
                  <div class="pricing mb-4">
                    <span class="fw-bold h5">Pricing</span><br>
                    <div class="pricing-list">
                       <a href="" class="text-decoration-none">Pricing List</a>
                    </div>
                  </div>
                  
            </div>
              
            </div>
        </div>
        <div class="content col-lg-10">
            {{ $slot }}
        </div>
       </div>

  
    <script>
        $('#title-free-api').click(function(){
            $('.free-api-list').toggle('fast')
        })
        $('#title-genre-api').click(function(){
            $('.genre-list').toggle('fast')
        })
        $('#title-anime-list').click(function(){
            $('.anime-list').toggle('fast')
        })
        $('#title-anime-vip').click(function(){
            $('.anime-vip-list').toggle('fast')
        })
        $('#title-anime-rating-point').click(function(){
            $('.send-rating').toggle('fast')
        })
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
    {{-- 
          <script type="text/javascript">window.$crisp=[];window.CRISP_WEBSITE_ID="f4ea2698-78dc-4d71-abb0-7ea7cd3631c2";(function(){d=document;s=d.createElement("script");s.src="https://client.crisp.chat/l.js";s.async=1;d.getElementsByTagName("head")[0].appendChild(s);})();</script>    
    --}}
</body>
</html>