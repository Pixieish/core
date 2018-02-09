<?php
/** Freesewing\Patterns\Magpie\CircleSkirt class */
namespace Freesewing\Patterns\Magpie;

use Freesewing\Utils;
use Freesewing\BezierToolbox;

/**
 * A Circle Skirt
 *
 * @author Pixie Mitchell extending the hard work of Joost De Cock <joost@decock.org>
 * @copyright 2018 Joost De Cock and Pixie Mitchell
 * @license http://opensource.org/licenses/GPL-3.0 GNU General Public License, Version 3
 */
class CircleSkirt extends \Freesewing\Patterns\Core\Pattern
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
        //$this->setValue('skirtQuarter', $model->m('naturalWaist')/4);
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
        $this->draftSkirt($model);
        $this->draftSkirtQuarter($model);
        $this->draftSkirtFront($model);
        $this->draftwaistBand($model);
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
        $this->finalizeSkirt($model);
        $this->finalizeSkirtQuarter($model);
        $this->finalizeSkirtFront($model);
        $this->finalizewaistBand($model);
        
        // Is this a paperless pattern?
        if ($this->isPaperless) {
            // Add paperless info to our example part
            $this->paperlessSkirt($model);
            $this->paperlessSkirtQuarter($model);
            $this->paperlessSkirtFront($model);
            $this->paperlesswaistBand($model);
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
    public function draftSkirt($model)
    {
        /** @var \Freesewing\Part $p */
        $p = $this->parts['skirt'];

        $skirtSit = (($model->m('hipsCircumference')-$model->m('naturalWaist'))/2)+ $model->m('naturalWaist');
      
        $this->setValue('skirtRadiusFactor', 100);
        $delta = 1;
        do {
            if($delta > 0) {
                $this->setValue('skirtRadiusFactor', $this->getValue('skirtRadiusFactor') * 0.99);
            } else {
                $this->setValue('skirtRadiusFactor', $this->v('skirtRadiusFactor') * 1.015);
            }
            
            $rad = BezierToolbox::bezierCircle($this->v('skirtRadiusFactor'));

            $p->newPoint('zero',0,0,'this is the middle zero xy');
            $p->newPoint(1,0,$this->v('skirtRadiusFactor'), 'skirt Bottom - of opening');
            $p->newPoint(2,$this->v('skirtRadiusFactor'),0,'skirt Right - of opening');
            //$p->addPoint(3,$p->shift(1,0,$p->x(2)/2),'Right control point for skirt Bottom');
            $p->newPoint(3,0+$rad,$this->v('skirtRadiusFactor'),'Right control point for skirt Bottom' );
            //$p->addPoint(4,$p->shift(2,-90,$p->y(1)/2), 'Bottom control point for skirt Right');
            $p->newPoint(4,$this->v('skirtRadiusFactor'),0+$rad,'Bottom control point for skirt Right' );
            
            //Attempts to get an eighth circle curve
            //rotating original point 45 degrees anticlockwise
            $p->addPoint('New2', $p->rotate(1,'zero',45),'This should duplicate new1');
            //adding first control point
            $p->newPoint('1cp',(0+$rad)/2,$this->v('skirtRadiusFactor') );
            // creating a control point down from horizontal
            $p->newPoint('2cptemp',$this->v('skirtRadiusFactor'),(0+$rad)/2 );
            //and rotating it 45 degrees clockwise
            $p->addPoint('2cp', $p->rotate('2cptemp','zero',-45));
            
            //original untouched code continues here 

            $delta = $this->skirtOpeningDelta($model, $p);
            $this->msg("Skirt Radius is $delta mm off");
        } while (abs($delta) > 1);

    
        // Mirror quarter opening around X axis
        $flip = [2,3,4];
        foreach($flip as $id) {
            $p->addPoint($p->newId('left'), $p->flipX($id, 0));
        }

        // Mirror half opening around Y axis
        $flip = [1,3,4,'left2','left3'];

        foreach($flip as $id) {
            $p->addPoint($p->newId('top'), $p->flipY($id, 0));
        }
        // Draw the skirt opening -full circle
        //$p->newPath('skirtOpening', 'M 1 C 3 4 2 C top3 top2 top1 C top4 top5 left1 C left3 left2 1 z');
        //$p->paths['skirtOpening']->setSample(true);
        //  Draw the skirt edge
            // Make radius of skirt edge  
            //I need to make $skirtLength an option
            $skirtLength = 300;
            $radius = $p->y(1) + $skirtLength +$this->o('lengthBonus');  
            
            // Bottom right corner
            $p->addPoint(5, $p->shift(1,-90,$radius),'Bottom');

            //I think something is still broken about how I'm using the bezier circle here??
            $p->addPoint(6, $p->shift(5,0, BezierToolbox::bezierCircle($radius) *0.6),'BottomRcp');
            $p->addPoint(7, $p->rotate(5,'zero',90),'Right');
            $p->addPoint('5cpup', $p->rotate(6,'zero',90),'RightTcp');

            $p->addPoint(8, $p->flipY('5cpup',0),'RightBcp');

            $p->addPoint(9, $p->flipX(6,0),'BottomLcp');
            $p->addPoint(10, $p->flipX(7,0),'Left');
            $p->addPoint(11, $p->flipX(8,0),'LeftBcp');

            $p->addPoint(21, $p->rotate(9,'zero',-45),'MidLBcp');
            $p->addPoint(22, $p->rotate(5,'zero',-45),'MidBL');
            $p->addPoint(23, $p->rotate(8,'zero',-45),'MidBLcp');

            $p->addPoint(24, $p->flipX(21,0),'MidRBcp');
            $p->addPoint(25, $p->flipX(22,0),'MidBR');
            $p->addPoint(26, $p->flipX(23,0),'MIdBRcp');
            
            // points for cut on fold lines
            $p->addPoint('1shift', $p->shift(1,-90,30));
            $p->addPoint('5shift', $p->shift(5,90,30));

            
        

        //$p->offsetPath('skirtEdge', 'skirtOpening',-($model->m('hipsToUpperLeg')+$model->m('naturalWaistToHip')+ $this->o('lengthBonus')), true, ['class' => 'fabric']);
    }
    
    protected function skirtOpeningDelta($model, $part)
    
    {
        $skirtSit = (($model->m('hipsCircumference')-$model->m('naturalWaist'))/2)+ $model->m('naturalWaist');
        $length = $part->curveLen(1,3,4,2) * 4;
        //the length of the quarter bezier curve multiplied by 4
        $target = $skirtSit;
        //the target length of the natural waist 
        return $length - $target;
        //says what the difference is 
    }

    public function draftSkirtQuarter($model)
    {
        /** @var \Freesewing\Part $p */
        $p = $this->parts['skirtQuarter'];

        $this->clonePoints('skirt','skirtQuarter');

        //New line to divide quarter skirt  
        //$p->newPath('quartDivide', 'M 25 L zero');
        //$p->paths['quartDivide']->setSample(true);

        $p->curveCrossesLine(1,3,4,2,25,'zero','new');
       
        // Draw the skirt quarter
        $p->newPath('skirtQuarterLine', 'M 1 C 1cp 2cp New2 L 25 C 23 6 5 z');
        $p->paths['skirtQuarterLine']->setSample(true);
        
    }
    public function draftSkirtFront($model)
    {
        /** @var \Freesewing\Part $p */
        $p = $this->parts['skirtFront'];

        $this->clonePoints('skirt','skirtFront');
       
        // Draw the skirt front
        $p->newPath('skirtFrontLine', 'M 1 C 3 4 2 L 7 C 8 24 25 C 23 6 5 z');
        $p->paths['skirtFrontLine']->setSample(true);
       
    }
    // public function draftSkirtFrontOld ($model)
    // {
    //     /** @var \Freesewing\Part $p */
    //     $p = $this->parts['skirtFrontOld'];

    //     $this->clonePoints('skirt','skirtFrontOld');
       
    //     // Draw the skirt front
    //     $p->newPath('skirtFrontOldLine', 'M left1 C left3 left2 1 C 3 4 2 L 7 C 8 24 25 C 23 6 5 C 9 26 22 C 21 11 10 z');
    //     $p->paths['skirtFrontOldLine']->setSample(true);
        
    // }
    public function draftwaistBand($model)
    {
        /** @var \Freesewing\Part $p */
        $p = $this->parts['waistBand'];
        
        $this->clonePoints('skirt','waistBand');

        $waistBandDepth = $model->m('naturalWaistToHip')*0.55; 
        

        $p->addPoint('W2',$p->shift('zero',0, ($p->curveLen(1,3,4,2)*4),'WaistTR'));
        //note - could probably replace the curvelen with $skirtSit
        $p->addPoint('W4',$p->shift('zero',-90, $waistBandDepth),'WaistBL');
        $p->newPoint('W3',$p->x('W2'),$p->y('W4'),'WaistBR');
       

        // Draw the waist band
        $p->newPath('waistBand', 'M zero L W2 L W3 L W4 z');
        $p->paths['waistBand']->setSample(true);
        
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
    public function finalizeSkirt($model)
    {
        /** @var \Freesewing\Part $p */
        $p = $this->parts['skirt'];
    }

    public function finalizeSkirtQuarter($model)
    {
        /** @var \Freesewing\Part $p */
        $p = $this->parts['skirtQuarter'];

        if($this->o('sa')) {
            // Seam allowance 
            // some more sa mess
            $p->offsetPathString('Qwaistsa','M 1 C 1cp 2cp New2',$this->o('sa')*1,1, ['class' => 'fabric sa']);
            $p->newPath('Qsaside1', 'M 1 L Qwaistsa-startPoint', ['class' => 'fabric sa']);
            $p->newPath('Qsaside2', 'M New2 L Qwaistsa-endPoint', ['class' => 'fabric sa']);

            $p->offsetPathString('Qedgesa','M New2 L 25',$this->o('sa')*1,1, ['class' => 'fabric sa']);
            $p->newPath('Qsaedge1', 'M New2 L Qedgesa-startPoint', ['class' => 'fabric sa']);
            $p->newPath('Qsaedge2', 'M 25 L Qedgesa-endPoint', ['class' => 'fabric sa']);
         }

        $p->newCutonfold('5shift','1shift', $this->t('Cut on fold'));
        // Title
        $p->newPoint('titleAnchor1', $p->x(2), $p->y(5)/2, 'Title anchor');
        $p->addTitle('titleAnchor1', 2, $this->t($p->title),'2x');

        // Logo
        $p->addPoint('logoAnchor', $p->shift('titleAnchor1',-90,30));
        $p->newSnippet('logo', 'logo-sm', 'logoAnchor');
      
    }
    public function finalizeSkirtFront($model)
    {
        /** @var \Freesewing\Part $p */
        $p = $this->parts['skirtFront'];

        if($this->o('sa')) {
            // Seam allowance 
            // some more sa mess
            $p->offsetPathString('waistsa','M 1 C 3 4 2',$this->o('sa')*1,1, ['class' => 'fabric sa']);
            $p->newPath('saside1', 'M 1 L waistsa-startPoint', ['class' => 'fabric sa']);
            $p->newPath('saside2', 'M 2 L waistsa-endPoint', ['class' => 'fabric sa']);

            $p->offsetPathString('edgesa','M 2 L 7',$this->o('sa')*1,1, ['class' => 'fabric sa']);
            $p->newPath('saedge1', 'M 2 L edgesa-startPoint', ['class' => 'fabric sa']);
            $p->newPath('saedge2', 'M 7 L edgesa-endPoint', ['class' => 'fabric sa']);
         }
        // Cut on fold
               
        $p->newCutonfold('5shift','1shift', $this->t('Cut on fold'));
         // Title
         $p->newPoint('titleAnchor', $p->x(2), $p->y(5)/2, 'Title anchor');
         $p->addTitle('titleAnchor', 1, $this->t($p->title),'1x');
 
         // Logo
         $p->addPoint('logoAnchor', $p->shift('titleAnchor',-90,30));
         $p->newSnippet('logo', 'logo-sm', 'logoAnchor');
       
    }
    public function finalizewaistBand($model)
    {
        /** @var \Freesewing\Part $p */
        $p = $this->parts['waistBand'];

        if($this->o('sa')) {
            // Seam allowance 
            // some more sa mess
            $p->offsetPathString('waistbandsa','M zero L W2 L W3 L W4 L zero z',$this->o('sa')*1,1, ['class' => 'fabric sa']);
            //$p->offsetPath( 'outline','waistBand', 10, true, ['class' => 'fabric sa']);
         }
        
        //orig starts back here
        $p->newCutonfold(2,7, $this->t('Cut on fold'));
        // Title
        $p->newPoint('titleAnchor', $p->x('W2')/2, $p->y('W4')*0.8, 'Title anchor');
        $p->addTitle('titleAnchor', 3, $this->t($p->title),'1x');  
        
 
        // Logo
        $p->addPoint('logoAnchor', $p->shift('titleAnchor',45,30));
        $p->newSnippet('logo', 'logo-sm', 'logoAnchor');
       
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
    public function paperlessSkirt($model)
    {
        /** @var \Freesewing\Part $p */
        $p = $this->parts['skirt'];
    }
    public function paperlessSkirtQuarter($model)
    {
        /** @var \Freesewing\Part $p */
        $p = $this->parts['skirtQuarter'];
    }
    public function paperlessSkirtFront($model)
    {
        /** @var \Freesewing\Part $p */
        $p = $this->parts['skirtFront'];
    }
    public function paperlesswaistBand($model)
    {
        /** @var \Freesewing\Part $p */
        $p = $this->parts['waistBand'];
    }
}
