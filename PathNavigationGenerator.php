<?php

namespace Tn\Bundle\PathNavigationBundle;

use Sculpin\Core\Generator\GeneratorInterface;
use Sculpin\Core\Source\SourceInterface;
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
        PathNavigationProvider $PathNavigationProvider,
        PermalinkFactory $permalinkFactory
    ) {
        $this->dataProvider = $dataProviderManager;
        $this->PathNavigationProvider = $PathNavigationProvider;
        $this->permalinkFactory = $permalinkFactory;
    }

    /**
     * {@inheritDoc}
     */
    public function generate(SourceInterface $source)
    {
        $generatedYears = array();
        $generatedMonths = array();
        $generatedSources = array();

        $datedPostData = $this->PathNavigationProvider->provideData();

        foreach ($this->dataProvider->provideData() as $post) {
            $date = \DateTime::createFromFormat('U', 0);
            if ($post->date() !== "") {
                $date = \DateTime::createFromFormat('U', $post->date());
            }

            $year = $date->format('Y');
            $month = $date->format('m');

            if (in_array($year.'-'.$month, $generatedMonths)) {
                continue;
            }

            $monthGeneratedSource = $source->duplicate(
                $source->sourceId().':year='.$year.':month='.$month
            );
            $monthGeneratedSource->data()->set('permalink', $this->permalinkFactory->getMonth($year, $month));
            $monthGeneratedSource->data()->set('year', $year);
            $monthGeneratedSource->data()->set('month', $month);
            $monthGeneratedSource->data()->set('path_posts', $datedPostData[$year]['months'][$month]['posts']);
            $generatedMonths[] = $year.'-'.$month;
            $generatedSources[] = $monthGeneratedSource;

            if (in_array($year, $generatedYears)) {
                continue;
            }

            $yearGeneratedSource = $source->duplicate(
                $source->sourceId().':year='.$year
            );
            $yearGeneratedSource->data()->set('permalink', $this->permalinkFactory->getYear($year));
            $yearGeneratedSource->data()->set('year', $year);
            $yearGeneratedSource->data()->set('path_posts', $datedPostData[$year]['posts']);
            $generatedYears[] = $year;
            $generatedSources[] = $yearGeneratedSource;
        }

        return $generatedSources;
    }
}
