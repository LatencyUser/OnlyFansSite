<footer class="footer py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mb-4 mx-auto text-center">
                <a href="{{route('contact')}}" class="text-secondary text-lg m-2">
                    {{__('Contact page')}}
                </a>
                @foreach(GenericHelper::getFooterPublicPages() as $page)
                    <a href="{{route('pages.get',['slug' => $page->slug])}}" target="" class="text-secondary text-lg m-2">
                        {{__($page->title)}}
                    </a>
                @endforeach
            </div>
            <div class="col-lg-8 mx-auto text-center mb-3 mt-2">
                <div class="d-flex justify-content-center">
                    @if(getSetting('social-media.facebook_url'))
                        <a class="m-2" href="{{getSetting('social-media.facebook_url')}}" target="_blank">
                            @include('elements.icon',['icon'=>'logo-facebook','variant'=>'medium','classes' => 'opacity-8'])
                        </a>
                    @endif
                    @if(getSetting('social-media.twitter_url'))
                        <a class="m-2" href="{{getSetting('social-media.twitter_url')}}" target="_blank">
                            @include('elements.icon',['icon'=>'logo-twitter','variant'=>'medium','classes' => 'opacity-8'])
                        </a>
                    @endif
                    @if(getSetting('social-media.instagram_url'))
                        <a class="m-2" href="{{getSetting('social-media.instagram_url')}}" target="_blank">
                            @include('elements.icon',['icon'=>'logo-instagram','variant'=>'medium','classes' => 'opacity-8'])
                        </a>
                    @endif
                    @if(getSetting('social-media.whatsapp_url'))
                        <a class="m-2" href="{{getSetting('social-media.whatsapp_url')}}" target="_blank">
                            @include('elements.icon',['icon'=>'logo-whatsapp','variant'=>'medium','classes' => 'opacity-8'])
                        </a>
                    @endif
                    @if(getSetting('social-media.tiktok_url'))
                        <a class="m-2" href="{{getSetting('social-media.tiktok_url')}}" target="_blank">
                            @include('elements.icon',['icon'=>'logo-tiktok','variant'=>'medium','classes' => 'opacity-8'])
                        </a>
                    @endif
                    @if(getSetting('social-media.youtube_url'))
                        <a class="m-2" href="{{getSetting('social-media.youtube_url')}}" target="_blank">
                            @include('elements.icon',['icon'=>'logo-youtube','variant'=>'medium','classes' => 'opacity-8'])
                        </a>
                    @endif
                    @if(getSetting('social-media.telegram_link'))
                        <li class="nav-item">
                            <a class="nav-link pe-1" href="{{getSetting('social-media.telegram_link')}}" target="_blank">
                                @include('elements.icon',['icon'=>'paper-plane','variant'=>'medium','classes' => 'text-lg opacity-8'])
                            </a>
                        </li>
                    @endif
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-8 mx-auto text-center mt-1">
                <p class="mb-0 text-secondary">
                    {{__("Copyright")}} © {{date('Y')}} {{getSetting('site.name')}}. {{__('All rights reserved.')}}
                </p>

            </div>
        </div>
    </div>
</footer>
