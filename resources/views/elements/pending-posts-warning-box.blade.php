<div class="alert alert-primary alert-dismissable text-white unverified-email-box mb-0" role="alert">
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
    <p class="m-0">
        {{trans_choice("You only have :approved approved posts.", PostsHelper::getUserApprovedPostsCount(Auth::user()->id),['approved' => PostsHelper::getUserApprovedPostsCount(Auth::user()->id)])}}
        {{trans_choice("Your next :tbapproved posts will have to admin approved.", PostsHelper::getPostsCountLeftTillAutoApprove(Auth::user()->id),['tbapproved' => PostsHelper::getPostsCountLeftTillAutoApprove(Auth::user()->id)])}} </p>
</div>
