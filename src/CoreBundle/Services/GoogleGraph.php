<?php

namespace CoreBundle\Services;

use CoreBundle\Entity\User;
use CoreBundle\Entity\Sujet;
use BackendBundle\Entity\Theme;
use CoreBundle\Entity\PostLike;
use BackendBundle\Entity\Univers;
use BackendBundle\Entity\Activity;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Routing\RouterInterface;
use CMEN\GoogleChartsBundle\GoogleCharts\Charts;
use Doctrine\Common\Collections\ArrayCollection;
use GoogleCharts\Options\ComboChart\ComboChartOptions;
use CMEN\GoogleChartsBundle\GoogleCharts\Charts\PieChart;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use CMEN\GoogleChartsBundle\GoogleCharts\Charts\ComboChart;
use CMEN\GoogleChartsBundle\GoogleCharts\Charts\ColumnChart;
 

class GoogleGraph extends Controller
{
    
    /**
     * Undocumented variable
     *
     * @var ObjectManager
     */
    private $manager;

    public function __construct(ObjectManager $manager)
    {
        $this->manager = $manager;
    }

    public function getActivityUnivers(User $user, $universList)
    {
        
        $activities     = null;
        $data           = array();
        $columnChart    = null;
        $message        = null;
        $mostView       = null;
        $nbActivities   = $this->manager->getRepository(Activity::class)->getActivitiesByUser($user);
        $data[]         = ['Label', 'Consultation(s)'];

        if ( $nbActivities > 0 ) {

            $columnChart = new ColumnChart();

            foreach ($universList as $univers) {
        
                $count = $this->manager->getRepository(Activity::class)->getUniversActivityByUser($user, $univers); 
                $data[] =  [$univers->getLibelle(), (int)$count];

                if ( $count > $mostView || null === $mostView ) {

                    $message = "Votre univers le plus consulté est ".$univers->getLibelle()." avec un nombre de ".$count." consultation.";
                    $mostView = $count;

                }
            
            }

            $columnChart->getData()->setArrayToDataTable($data);
            $columnChart->getOptions()->setTitle('Mes consultations par univers');
            $columnChart->getOptions()->setBackgroundColor('none');
            $columnChart->getOptions()->setHeight(500);
            $columnChart->getOptions()->setWidth('100%');
            $columnChart->getOptions()->getTitleTextStyle()->setColor('#668da3');
            $columnChart->getOptions()->getTitleTextStyle()->setFontSize(20);
            $columnChart->getOptions()->getLegend()->getTextStyle()->setColor('#668da3');
            $columnChart->getOptions()->getHAxis()->getTextStyle()->setColor('#668da3');
            $columnChart->getOptions()->getVAxis()->getTextStyle()->setColor('#668da3');

        }

        return $columnChart;

    }

    public function getActivityTheme(User $user, $universList)
    {
       
        $activities     = null;
        $data           = array();
        $columnChart    = null;
        $nbActivities   = $this->manager->getRepository(Activity::class)->getActivitiesByUser($user);
        $data[]         = ['Label', 'Consultation(s)'];

        if ( $nbActivities > 0 ) {

            $columnChart = new ColumnChart();

            foreach ($universList as $univers) {

                $themesList = $this->manager->getRepository(Theme::class)->findBy([
                    'univers'   =>  $univers,
                ]);

                foreach ($themesList as $theme) {

                    $count = $this->manager->getRepository(Activity::class)->getThemeActivityByUser($user, $theme); 
                    $data[] =  [$theme->getLibelle(), (int)$count];
                
                }

            }
                
            $columnChart->getData()->setArrayToDataTable($data);
            $columnChart->getOptions()->setTitle('Mes consultations par thème');
            $columnChart->getOptions()->setBackgroundColor('none');
            $columnChart->getOptions()->setHeight(500);
            $columnChart->getOptions()->setWidth('100%');
            $columnChart->getOptions()->getTitleTextStyle()->setColor('#668da3');
            $columnChart->getOptions()->getTitleTextStyle()->setFontSize(20);
            $columnChart->getOptions()->getLegend()->getTextStyle()->setColor('#668da3');
            $columnChart->getOptions()->getHAxis()->getTextStyle()->setColor('#668da3');
            $columnChart->getOptions()->getVAxis()->getTextStyle()->setColor('#668da3');

        }

        return $columnChart;

    }

    public function getPieChartLike(User $user)
    {
       
        $activities     = null;
        $data           = array();
        $pieChart       = null;
        $nbLikes        = $this->manager->getRepository(PostLike::class)->countLikeByUser($user);
        $nbDislikes     = $this->manager->getRepository(PostLike::class)->countDislikeByUser($user);
           
        if ( $nbLikes > 0 || $nbDislikes > 0 ) {

            $pieChart = new PieChart();     
            
            $data[] = ['Label', 'Value'];
            $data[] = ['Like(s)', (int)$nbLikes];
            $data[] = ['Dislike(s)', (int)$nbDislikes];

            $pieChart->getData()->setArrayToDataTable(
                    $data
            );
         
            $pieChart->getOptions()->setTitle('Vos commentaires :');
            $pieChart->getOptions()->setBackgroundColor('none');
            $pieChart->getOptions()->setHeight(500);
            $pieChart->getOptions()->setWidth('100%');
            $pieChart->getOptions()->getTitleTextStyle()->setColor('#668da3');
            $pieChart->getOptions()->getTitleTextStyle()->setFontSize(20);
            $pieChart->getOptions()->getLegend()->getTextStyle()->setColor('#668da3');
        }

        return $pieChart;

    }

}