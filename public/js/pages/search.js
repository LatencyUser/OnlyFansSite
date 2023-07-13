/*
* Feed page & component
 */
"use strict";
/* global  SuggestionsSlider, Post, initStickyComponent, paginatorConfig, PostsPaginator, redirect, app, searchType,initialPostIDs, getCookie, UsersPaginator, StreamsPaginator  */

$(function () {

    if(searchType === 'feed'){
        if(typeof paginatorConfig !== 'undefined'){
            if((paginatorConfig.total > 0 && paginatorConfig.total > paginatorConfig.per_page) && paginatorConfig.hasMore) {
                PostsPaginator.initScrollLoad();
            }
            PostsPaginator.init(paginatorConfig.next_page_url, '.posts-wrapper');
        }
        else{
            // eslint-disable-next-line no-console
            console.error('Pagination failed to initialize.');
        }
        PostsPaginator.initPostsGalleries(initialPostIDs);
        Post.setActivePage('search');
        if(getCookie('app_prev_post') !== null){
            PostsPaginator.scrollToLastPost(getCookie('app_prev_post'));
        }
        Post.initPostsMediaModule();
    }

    if(searchType === 'people') {
        if(typeof paginatorConfig !== 'undefined'){
            if((paginatorConfig.total > 0 && paginatorConfig.total > paginatorConfig.per_page) && paginatorConfig.hasMore) {
                UsersPaginator.initScrollLoad();
            }
            UsersPaginator.init(paginatorConfig.next_page_url, '.users-wrapper');
        }
        else{
            // eslint-disable-next-line no-console
            console.error('Pagination failed to initialize.');
        }
        Search.initSearchFilterLiveReloads();
    }

    if(searchType === 'streams') {
        if(typeof paginatorConfig !== 'undefined'){
            if((paginatorConfig.total > 0 && paginatorConfig.total > paginatorConfig.per_page) && paginatorConfig.hasMore) {
                StreamsPaginator.initScrollLoad();
            }
            StreamsPaginator.init(paginatorConfig.next_page_url, '.streams-wrapper');
        }
        else{
            // eslint-disable-next-line no-console
            console.error('Pagination failed to initialize.');
        }
        Search.initSearchFilterLiveReloads();
    }

    SuggestionsSlider.init('.suggestions-box-mobile');
    SuggestionsSlider.init('.suggestions-box');
});

$(window).scroll(function () {
    initStickyComponent('.search-widgets','sticky');
});

// eslint-disable-next-line no-unused-vars
var Search = {

    goBack: function () {
        redirect(app.baseUrl+'/feed');
    },

    initSearchFilterLiveReloads: function () {
        $('.search-filters-form input, .search-filters-form select').on('change',function () {
            $('.search-filters-form').submit();
        });

        $('.search-filters-form input').keypress(function (e) {
            if (e.which === 13) {
                $('.search-filters-form').submit();
                return false;
            }
        });
    }

};
