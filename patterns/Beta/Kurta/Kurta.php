<?php
/** Freesewing\Patterns\Magpie\Kurta class */
namespace Freesewing\Patterns\Magpie;

use Freesewing\Utils;
use Freesewing\BezierToolbox;

/**
 * A pattern template
 *
 * If you'd like to add you own pattern, you can copy this class/directory.
 * It's an empty skeleton for you to start working with
 *
 * @author Joost De Cock <joost@decock.org>
 * @copyright 2017 Joost De Cock
 * @license http://opensource.org/licenses/GPL-3.0 GNU General Public License, Version 3
 */
class Kurta extends \Freesewing\Patterns\Core\Pattern
{
    /*
        ___       _ _   _       _ _
       |_ _|_ __ (_) |_(_) __ _| (_)___  ___
        | || '_ \| | __| |/ _` | | / __|/ _ \
        | || | | | | |_| | (_| | | \__ \  __/
       |___|_| |_|_|\__|_|\__,_|_|_|___/\___|

      Things we need to do before we can draft a pattern
    */

    /**
     * Set your constants here
     */
    //const EXAMPLE_CONSTANT = 52;

    /**
     * Sets up options and values for our draft
     *
     * By branching this out of the sample/draft methods, we can
     * set a bunch of options and values the influence the draft
     * without having to touch the sample/draft methods
     * When extending this pattern so we can just implement the
     * initialize() method and re-use the other methods.
     *
     * Good to know:
     * Options are typically provided by the user, but sometimes they are fixed
     * Values are calculated for re-use later
     *
     * @param \Freesewing\Model $model The model to sample for
     *
     * @return void
     */
    public function initialize($model)
    {
        // You could fix options here. For example, when you extend a
        // pattern that has options that you don't want to offer to users
        //$this->setOptionIfUnset('percentOption', self::EXAMPLE_CONSTANT);

        // Set values for use later
        //$this->setValue('exampleValue', time());
    }

    /*
        ____             __ _
       |  _ \ _ __ __ _ / _| |_
       | | | | '__/ _` | |_| __|
       | |_| | | | (_| |  _| |_
       |____/|_|  \__,_|_|  \__|

      The actual sampling/drafting of the pattern
    */

    /**
     * Generates a sample of the pattern
     *
     * Here, you create a sample of the pattern for a given model
     * and set of options. You should get a barebones pattern with only
     * what it takes to illustrate the effect of changes in
     * the sampled option or measurement.
     *
     * @param \Freesewing\Model $model The model to sample for
     *
     * @return void
     */
    public function sample($model)
    {
        // Setup all options and values we need
        $this->initialize($model);

        // Draft our example part
        $this->draftKurtaFront($model);
        $this->draftKurtaBack($model);
        $this->draftNeckFacing($model);
        $this->draftBackFacing($model);
        
    }

    /**
     * Generates a draft of the pattern
     *
     * Here, you create the full draft of this pattern for a given model
     * and set of options. You get a complete pattern with
     * all bels and whistles.
     *
     * @param \Freesewing\Model $model The model to draft for
     *
     * @return void
     */
    public function draft($model)
    {
        // Continue from sample
        $this->sample($model);

        // Finalize our example part
        $this->finalizeKurtaFront($model);
        $this->finalizeKurtaBack($model);
        $this->finalizeNeckFacing($model);
        $this->finalizeBackFacing($model);
        
        // Is this a paperless pattern?
        if ($this->isPaperless) {
            // Add paperless info to our example part
            $this->paperlessKurtaFront($model);
            $this->paperlessKurtaBack($model);
            $this->paperlessNeckFacing($model);
            $this->paperlessBackFacing($model);
        }
    }

    /**
     * Drafts the examplePart
     *
     * We are using a draft[part name] scheme here but
     * don't let that think that this is something specific
     * to the draft service.
     *
     * This draft method does the basic drafting and is
     * called by both the draft AND sample methods.
     *
     * The difference starts after this method is done.
     * For sample, this is all we need, but draft calls
     * the finalize[part name] method after this.
     *
     * @param \Freesewing\Model $model The model to draft for
     *
     * @return void
     */
    public function draftKurtaFront($model)
    {
        /** @var \Freesewing\Part $p */
        $p = $this->parts['kurtaFront'];

       //note to self - make the multiplier on the $neckdepth an option addition
        $neckDepth=($model->m('neckCircumference')/4)*1.2;
        $rad = BezierToolbox::bezierCircle($neckDepth);
                

        $p->newPoint('zero',0,0,'this is the middle zero xy');
        $p->newPoint(1,($model->m('neckCircumference')/4)*0.8,0, 'neck Right - of opening');

        //to visualise the neck 
        $p->newPoint(100,$model->m('neckCircumference')/(2*3.14),0, 'neck Right absolute');


        $p->newPoint(4,0,$neckDepth,'neck Bottom - of opening');
        $p->newPoint(3,0+$rad,$neckDepth,'Right control point for neck Bottom' );
        $p->newPoint(2,$model->m('neckCircumference')/4*0.8,0+$rad,'Bottom control point for neck Right' );
        // point 5 measurement was originall half shoulderToShoulder but I had more sample model measurements for acrossBack 
        $p->addPoint(5,$p->shift('zero',0,($model->m('acrossBack')/2)+10),'shoulder');
              
        $p->newPoint(6,0,($model->m('neckCircumference')*0.05),'neck Bottom - of opening');
        $p->addPoint(8,$p->shift(6,-90, $model->m('centerBackNeckToWaist')),'waist');
        $p->addPoint(7,$p->shift(8,90, $model->m('naturalWaistToUnderbust')),'underbust');
        $p->addPoint(9,$p->shift(8,-90, $model->m('naturalWaistToHip')),'hip');
        $p->addPoint(17,$p->shift(7,90, 60),'bust');
        $p->addPoint(18,$p->shift(9,-90, $this->o('lengthBonus')),'hem');

        

        $p->addPoint(10,$p->shift(8,0, $model->m('naturalWaist')/4+($this->o('waistEase') / 4)),'waistedge');
        $p->addPoint(11,$p->shift(7,0, $model->m('underbust')/4 + ($this->o('chestEase') / 4)),'underbustedge');
        $p->addPoint(12,$p->shift(9,0, $model->m('hipsCircumference')/4+($this->o('hipEase') / 4)),'hipedge');
        $p->addPoint(13,$p->shift(17,0, $model->m('chestCircumference')/4+ $this->getOption('chestEase') / 4),'bustedge');
        $p->addPoint(19,$p->shift(18,0, ($model->m('hipsCircumference')/4+($this->o('hipEase') / 4))*1.07),'hemedge');


        $p->addPoint(14,$p->shift(5,-90, $model->m('shoulderSlope')),'shoulder slope');
        $p->addPoint(15,$p->shift(14,-90, $model->m('shoulderSlope')),'shoulder slope down cp');
        $p->addPoint(16,$p->shift(13,180, $model->m('underbust')/12),'armhole');


        $p->addPoint(20,$p->shift(19,180, $model->m('hipsCircumference')/32),'hemcurve');
        $p->addPoint(21,$p->shift(19,90, $model->m('hipsCircumference')/16),'hemcurve');
        $p->addPoint(22,$p->shift(21,90, $model->m('hipsCircumference')/16),'hemcurve');

        $p->addPoint(23,$p->rotate(10,12,180));
        $p->newPoint(24,$p->x(12)+($p->deltaX(12,23)/2),$p->y(12)+($p->deltaY(12,23)/2),'midpoint of 12 and 23');
        $p->addPoint(25,$p->shift(16,180, $model->m('underbust')/32),'back armholecp');

        
        $p->addPoint(30,$p->shift(4,-90, 25),'facing middle edge');   
        
        
        
          $p->newPath('kurtaFront', 'M 4 L zero L 1 C 2 3 4 L 7 L 8 L 9 L 18 C 20 19 21 C 22 24 12 C 10 10 splitC8 C splitC7 splitC6 13 C 16 15 14 L 1 ');
          $p->paths['kurtaFront']->setSample(true);

           //10 C 11 11 13 split curve 
        $p->splitCurve(10,11,11,13, 0.5,'splitC',true);
    }    

    public function draftKurtaBack($model)
    {
        /** @var \Freesewing\Part $p */
        $p = $this->parts['kurtaBack'];
        $this->clonePoints('kurtaFront','kurtaBack');
        $rad = BezierToolbox::bezierCircle($model->m('neckCircumference')*0.125);
        //($model->m('neckCircumference')*0.125) = (($model->m('neckCircumference')/4)*1.2)= $neckdepth

        $p->newPoint('1b',$model->m('neckCircumference')/4,0, 'neck Right - of opening');
        $p->newPoint('3b',0+$rad/2,$p->y(6),'Right control point for neck Bottom' );
        $p->newPoint('2b',$p->x('1'),0+$rad*0.6,'Bottom control point for neck Right' );

        //consider making the back armhole slightly longer for better armhole balence 

        $p->newPath('kurtaBack', 'M 6 L zero L 1 C 2b 3b 6 L 7 L 8 L 9 L 18 C 20 19 21 C 22 24 12 C 10 10 splitC8 C splitC7 splitC6 13 C 25 15 14 L 1');
        $p->paths['kurtaBack']->setSample(true);
    }

    public function draftNeckFacing($model)
    {
        /** @var \Freesewing\Part $p */
        $p = $this->parts['neckFacing'];
        $this->clonePoints('kurtaFront','neckFacing');
        $this->clonePoints('kurtaBack','neckFacing');

        

        // Mirror quarter opening around X axis
        $flip = [1,2,3];
        foreach($flip as $id) {
            $p->addPoint($p->newId('left'), $p->flipX($id, 0));
        }
        
        $p->newPath('neckFacing', 'M neckFacingedge-startPoint L 1 C 2 3 4 C left3 left2 left1 L neckFacingedge-endPoint');
        $p->offsetPathString('neckFacingedge','M 1 C 2 3 4 C left3 left2 left1 ', 25,1);
        $p->offsetPathString('neckFabricedge','M 1 C 2 3 4 C left3 left2 left1 ', 50,1);
        $p->addPoint(70,$p->shift('neckFabricedge-startPoint',90,$this->o('sa')*1),'rightfacing up');   
        $p->addPoint(71,$p->shift('neckFabricedge-endPoint',90, $this->o('sa')*1),'leftfacing up');   
        $p->newPath('neckFabric', 'M neckFabricedge-startPoint L 70 L 71 L  neckFabricedge-endPoint');
      
        $p->paths['neckFacing']->setSample(true);
        $p->paths['neckFabric']->setSample(true);
    }
    public function draftBackFacing($model)
    {
        /** @var \Freesewing\Part $p */
        $p = $this->parts['backFacing'];
        $this->clonePoints('kurtaFront','backFacing');
        $this->clonePoints('kurtaBack','backFacing');
                
        

        $length = ($p->curveLen(1,'2b', '3b', 6)*2)+$this->o('sa')*3;

        // $p->offsetPathString('neckFacingedge','M 1 C 2 3 4 C left3 left2 left1 ', 25,1);
        // $p->offsetPathString('neckFabricedge','M 1 C 2 3 4 C left3 left2 left1 ', 50,1);
        $p->addPoint(80,$p->shift('zero',-90,25),'back facing bottom');  
        $p->addPoint(82,$p->shift('zero',0,$length),'back facing top right');  
        $p->addPoint(81,$p->shift(82,-90,25),'back facing across');   
          
        
        $p->newPath('backFacing', 'M zero L 80 L 81 L 82 z ');
        $p->paths['backFacing']->setSample(true);
        
    }

    
    /*
       _____ _             _ _
      |  ___(_)_ __   __ _| (_)_______
      | |_  | | '_ \ / _` | | |_  / _ \
      |  _| | | | | | (_| | | |/ /  __/
      |_|   |_|_| |_|\__,_|_|_/___\___|

      Adding titles/logos/seam-allowance/grainline and so on
    */

    /**
     * Finalizes the example part
     *
     * @param \Freesewing\Model $model The model to draft for
     *
     * @return void
     */
    public function finalizeKurtaFront($model)
    {
        /** @var \Freesewing\Part $p */
        $p = $this->parts['kurtaFront'];
        if($this->o('sa')) {
            // Seam allowance 
            // M 14 L //1 C 2 3 4// M 7 L 8 L 9 L 18 C 20 19 21 C 22 24 //12 C 10 10 splitC8 C splitC7 splitC6 13// C 16 15 14
            $p->offsetPathString('FrontSidesa','M 12 C 10 10 splitC8 C splitC7 splitC6 13 ',$this->o('sa')*-1,1, ['class' => 'fabric sa']);
            $p->offsetPathString('FrontHemsa',' M  18 C 20 19 21 C 22 24 12 ',$this->o('sa')*-1,1, ['class' => 'fabric sa']);
            $p->offsetPathString('FrontNecksa','M 1 C 2 3 4',$this->o('sa')*-1,1, ['class' => 'fabric sa']);
            $p->offsetPathString('FrontArmsa','M 13 C 16 15 14',$this->o('sa')*-1,1, ['class' => 'fabric sa']);
            $p->offsetPathString('FrontShouldersa','M 14 L 1',$this->o('sa')*-1,1, ['class' => 'fabric sa']);
            $p->offsetPathString('Frontneckguide','M zero L 1',$this->o('sa')*1,1, ['class' => 'fabric sa']);

            $p->newPoint('50',$p->x(14)+$this->o('sa')*1.1,$p->y(14)-$this->o('sa')*0.9,'right shouldersa' );
            $p->newPoint('51',$p->x(1)-$this->o('sa')*1,$p->y(1)-$this->o('sa')*1,'right shouldersa' );
            $p->newPoint('52',$p->x(13)+$this->o('sa')*1.4,$p->y(13)-$this->o('sa')*1,'right shouldersa' );
            $p->newPath('necktoshoulder', 'M FrontNecksa-startPoint L 51 L FrontShouldersa-endPoint', ['class' => 'fabric sa']);
            $p->newPath('shouldertoarms', 'M FrontShouldersa-startPoint L 50 L FrontArmsa-endPoint', ['class' => 'fabric sa']);
            $p->newPath('armstoside', 'M FrontArmsa-startPoint L 52 L FrontSidesa-endPoint', ['class' => 'fabric sa']);
            $p->newPath('neck', 'M 4 L FrontNecksa-endPoint', ['class' => 'fabric sa']);
            $p->newPath('hemsa', 'M 18 L FrontHemsa-startPoint', ['class' => 'fabric sa']);

            

            
         }
         
         
        $p->newCutonfold(9,30, $this->t('Cut on fold'));
        // Title
        $p->newPoint('titleAnchor1', $p->x(1), $p->y(7), 'Title anchor');
        $p->addTitle('titleAnchor1',1, $this->t($p->title),'1x');

        // Logo
        $p->addPoint('logoAnchor', $p->shift('titleAnchor1',-90,30));
        $p->newSnippet('logo', 'logo-sm', 'logoAnchor');
    }

    public function finalizeKurtaBack($model)
    {
        /** @var \Freesewing\Part $p */
        $p = $this->parts['kurtaBack'];

        if($this->o('sa')) {
            // Seam allowance 
            // M 14 L //1 C 2 3 4// M 7 L 8 L 9 L 18 C 20 19 21 C 22 24 //12 C 10 10 splitC8 C splitC7 splitC6 13// C 16 15 14
            $p->offsetPathString('FrontSidesa','M 12 C 10 10 splitC8 C splitC7 splitC6 13 ',$this->o('sa')*-1,1, ['class' => 'fabric sa']);
            $p->offsetPathString('FrontHemsa',' M  18 C 20 19 21 C 22 24 12 ',$this->o('sa')*-1,1, ['class' => 'fabric sa']);
            $p->offsetPathString('FrontNecksa','M 1 C 2b 3b 6',$this->o('sa')*-1,1, ['class' => 'fabric sa']);
            $p->offsetPathString('FrontArmsa','M 13 C 25 15 14',$this->o('sa')*-1,1, ['class' => 'fabric sa']);
            $p->offsetPathString('FrontShouldersa','M 14 L 1',$this->o('sa')*-1,1, ['class' => 'fabric sa']);
            $p->offsetPathString('Backneckguide','M zero L 1',$this->o('sa')*1,1, ['class' => 'fabric sa']);

             $p->newPoint('50',$p->x(14)+$this->o('sa')*1.2,$p->y(14)-$this->o('sa')*0.9,'right shouldersa' );
             $p->newPoint('51',$p->x(1)-$this->o('sa')*1,$p->y(1)-$this->o('sa')*1,'right shouldersa' );
             $p->newPoint('52',$p->x(13)+$this->o('sa')*1.4,$p->y(13)-$this->o('sa')*1,'right shouldersa' );
            $p->newPoint('53',$p->x(1)-$this->o('sa')*0.7,$p->y(1)-$this->o('sa')*1,'right shouldersa' );
            $p->newPath('necktoshoulder', 'M FrontNecksa-startPoint L 53 L FrontShouldersa-endPoint', ['class' => 'fabric sa']);
            $p->newPath('shouldertoarms', 'M FrontShouldersa-startPoint L 50 L FrontArmsa-endPoint', ['class' => 'fabric sa']);
            $p->newPath('arms', 'M FrontArmsa-startPoint L 52 L FrontSidesa-endPoint', ['class' => 'fabric sa']);
            $p->newPath('top', 'M 6 L FrontNecksa-endPoint', ['class' => 'fabric sa']);
            $p->newPath('hemsa', 'M 18 L FrontHemsa-startPoint', ['class' => 'fabric sa']);
            $p->newPath('backnecksa', 'M zero L Backneckguide-startPoint', ['class' => 'fabric sa']);
            
         }
         $p->newCutonfold(9,30, $this->t('Cut on fold'));
         // Title
         $p->newPoint('titleAnchor1', $p->x(1), $p->y(7), 'Title anchor');
         $p->addTitle('titleAnchor1',2, $this->t($p->title),'1x');
 
         // Logo
         $p->addPoint('logoAnchor', $p->shift('titleAnchor1',-90,30));
         $p->newSnippet('logo', 'logo-sm', 'logoAnchor');
    }
    
    public function finalizeNeckFacing($model)
    {
        /** @var \Freesewing\Part $p */
        $p = $this->parts['neckFacing'];
        // Title
        $p->addTitle(6,null, $this->t($p->title),'1x');

        // Logo
        $p->addPoint('logoAnchor', $p->shift(6,-90,30));
        $p->newSnippet('logo', 'logo-sm', 'logoAnchor');
    }
    public function finalizeBackFacing($model)
    {
        /** @var \Freesewing\Part $p */
        $p = $this->parts['backFacing'];
        // Title
        //$p->addTitle(6,4, $this->t($p->title),'1x');

        // Logo
        //$p->addPoint('logoAnchor', $p->shift(6,-90,30));
        //$p->newSnippet('logo', 'logo-sm', 'logoAnchor');
    }

    /*
        ____                       _
       |  _ \ __ _ _ __   ___ _ __| | ___  ___ ___
       | |_) / _` | '_ \ / _ \ '__| |/ _ \/ __/ __|
       |  __/ (_| | |_) |  __/ |  | |  __/\__ \__ \
       |_|   \__,_| .__/ \___|_|  |_|\___||___/___/
                  |_|

      Instructions for paperless patterns
    */

    /**
     * Adds paperless info for the example part
     *
     * @param \Freesewing\Model $model The model to draft for
     *
     * @return void
     */
    public function paperlessKurtaFront($model)
    {
        /** @var \Freesewing\Part $p */
        $p = $this->parts['kurtaFront'];
    }

    public function paperlessKurtaBack($model)
    {
        /** @var \Freesewing\Part $p */
        $p = $this->parts['kurtaBack'];
    }

    public function paperlessNeckFacing($model)
    {
        /** @var \Freesewing\Part $p */
        $p = $this->parts['neckFacing'];
    }

    public function paperlessBackFacing($model)
    {
        /** @var \Freesewing\Part $p */
        $p = $this->parts['backFacing'];
    }
}
