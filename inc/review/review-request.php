<?php

use PublishPress\WordPressReviews\ReviewsController;

class SeriesReview
{
    /**
    * @var  ReviewsController
    */
    private $reviewController;
    
    public function __construct()
    {
        $this->reviewController = new ReviewsController(
            'organize-series',
            'PublishPress Series',
            PPSERIES_URL . '/assets/images/icon-256x256.png'
        );
    }
    
    public function init()
    {
        // .......
        add_filter('publishpress_wp_reviews_display_banner_organize-series', [$this, 'shouldDisplayBanner']);
        
        $this->reviewController->init();
    }
    
    public function shouldDisplayBanner($shouldDisplay)
    {
        if(is_ppseries_admin_pages()){
            return true;
        }
        return false;
    }
}

add_action('plugins_loaded', 'pp_series_initiate_review');
function pp_series_initiate_review(){
    $review = new SeriesReview;
    $review->init();
}