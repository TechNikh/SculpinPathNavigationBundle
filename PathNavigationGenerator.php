<?php

namespace Tn\Bundle\PathNavigationBundle;

use Sculpin\Core\Generator\GeneratorInterface;
use Sculpin\Core\Source\SourceInterface;
use Sculpin\Core\Permalink\Permalink;
use Sculpin\Core\Permalink\SourcePermalinkFactory;
use Sculpin\Core\DataProvider\DataProviderInterface;
use Tn\Bundle\PathNavigationBundle\Permalink\PermalinkFactory;

/**
 * PathNavigationGenerator
 *
 * @author Jonathan Bouzekri <jonathan.bouzekri@gmail.com>
 */
class PathNavigationGenerator implements GeneratorInterface
{
    /**
     * @var \Sculpin\Core\DataProvider\DataProviderInterface
     */
    private $dataProvider;
    private $articlesDataProvider;

    /**
     *
     * @var \Tn\Bundle\PathNavigationBundle\PathNavigationProvider
     */
    private $PathNavigationProvider;

    /**
     * @var \Tn\Bundle\PathNavigationBundle\Permalink\PermalinkFactory
     */
    private $permalinkFactory;

    /**
     * Constructor
     *
     * @param \Sculpin\Core\DataProvider\DataProviderInterface $dataProviderManager
     * @param \Tn\Bundle\PathNavigationBundle\PathNavigationProvider $PathNavigationProvider
     */
    public function __construct(
        DataProviderInterface $dataProviderManager,
        DataProviderInterface $articlesDataProviderManager,
        PathNavigationProvider $PathNavigationProvider,
        PermalinkFactory $permalinkFactory
    ) {
        $this->dataProvider = $dataProviderManager;
        $this->articlesDataProvider = $articlesDataProviderManager;
        $this->PathNavigationProvider = $PathNavigationProvider;
        $this->permalinkFactory = $permalinkFactory;
    }
    
    private function processContentTypeDataGeneration($providerData, &$source, $datedPostData)
    {
      $generatedSources = array();
      foreach ($providerData as $post) {
        // Get post URL Path
        $sourcePermalinkFactory = new SourcePermalinkFactory('');
        $permalink = $sourcePermalinkFactory->create($post);
        $post_relative_url = $permalink->relativeUrlPath();
        $post_relative_url = trim($post_relative_url, '/');

        if(!$this->endsWith($post_relative_url, ".md")){
          continue;
        }
        $post_relative_url_array = explode('/', $post_relative_url);
        // Remove first two folders, & last file name
        unset($post_relative_url_array[0]);
        unset($post_relative_url_array[1]);
        array_pop($post_relative_url_array);
        $directory_relative_url = implode('/', $post_relative_url_array);

        $pathGeneratedSource = $source->duplicate(
            $source->sourceId().':path='."api/v1/categories/$directory_relative_url/"
            );
        $pathGeneratedSource->data()->set('permalink', "api/v1/categories/$directory_relative_url/");
        $pathGeneratedSource->data()->set('path', "api/v1/categories/$directory_relative_url/");

        $pathGeneratedSource->data()->set('api_posts', $datedPostData[$directory_relative_url]);
        $generatedSources[] = $pathGeneratedSource;
        /*$post_relative_url_array = explode('/', $post_relative_url);
        $length = count($post_relative_url_array);
        // 2 to account preceeding path: open-curricula-files/_videos in sculpin_kernel.yml
        $post_relative_url_array = array_splice($post_relative_url_array, 2, $length-3);
        for($i = 1; $i <= $length; $i++) {
          $clone_post_relative_url_array = $post_relative_url_array;
          $spliced_arr = array_splice($clone_post_relative_url_array, 0, $i);
          $spliced_relative_url = implode('/', $spliced_arr);
          
          $pathGeneratedSource = $source->duplicate(
              $source->sourceId().':path='."api/v1/categories/$spliced_relative_url/"
              );
          $pathGeneratedSource->data()->set('permalink', "api/v1/categories/$spliced_relative_url/");
          $pathGeneratedSource->data()->set('path', "api/v1/categories/$spliced_relative_url/");
          
          $post_data_url_array = explode('/', $spliced_relative_url);
          $clone_datedPostData = $datedPostData;
          foreach($post_data_url_array as $directory_name){
            $clone_datedPostData = $clone_datedPostData[$directory_name];
          }
          $pathGeneratedSource->data()->set('path_posts', $clone_datedPostData['posts']);
          $pathGeneratedSource->data()->set('api_posts', $clone_datedPostData['apis']);
          //$generatedPaths[] = "api/v1/categories/$spliced_relative_url/";
          $generatedSources[] = $pathGeneratedSource;
        }*/
      }
      return $generatedSources;
    }

    public function endsWith($haystack, $needle)
    {
      $length = strlen($needle);
      if ($length == 0) {
        return true;
      }

      return (substr($haystack, -$length) === $needle);
    }

    /**
     * {@inheritDoc}
     */
    public function generate(SourceInterface $source)
    {
        $generatedSources = array();
        $datedPostData = $this->PathNavigationProvider->provideData();

        $videosPathGeneratedSource = $this->processContentTypeDataGeneration($this->dataProvider->provideData(), $source, $datedPostData);
        $articlesPathGeneratedSource = $this->processContentTypeDataGeneration($this->articlesDataProvider->provideData(), $source, $datedPostData);
        
        //$generatedSources = $generatedSources + $videosPathGeneratedSource;
        //$generatedSources = $generatedSources + $articlesPathGeneratedSource;
        //$generatedSources[] = $articlesPathGeneratedSource;
        $generatedSources = array_merge_recursive($videosPathGeneratedSource, $articlesPathGeneratedSource);
        return $generatedSources;
    }
}
