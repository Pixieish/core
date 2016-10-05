<?php

namespace Freesewing\Patterns;

/**
 * Freesewing\Patterns\JoostBodyBlock class.
 *
 * @author Joost De Cock <joost@decock.org>
 * @copyright 2016 Joost De Cock
 * @license http://opensource.org/licenses/GPL-3.0 GNU General Public License, Version 3
 */
class JoostBodyBlock extends Pattern
{
    private $config_file = __DIR__.'/config.yml';
    public $parts = array();

    public function __construct()
    {
        $this->config = \Freesewing\Yamlr::loadConfig($this->config_file);
        
        return $this;
    }

    public function draft($model)
    {
        $this->help = array();
        $this->help['armholeDepth'] = 200 + ($model->getMeasurement('shoulderSlope')/2 - 27.5) + ($model->getMeasurement('upperBicepsCircumference')/10);
        $this->help['collarShapeFactor'] = 1;
        $this->help['sleevecapShapeFactor'] = 1;
        /*
        foreach ($this->config['parts'] as $part => $title) {
            $this->addPart($part);
            $this->parts[$part]->setTitle($title);
            $method = 'draft'.ucwords($part);
            $this->$method($model);
        }
        $this->tweakCollar($model);
        $this->tweakSleeve($model);
        //$this->parts['base']->setRender(false);
        
        $this->finalizeBody($model);
        $this->finalizeSleeve($model);
         */
        $this->addPart('base');
        $this->draftBase($model);
        
       }

    public function cleanUp()
    {
        unset($this->help);
    }

    private function draftBase($model) 
    {
        $collarWidth = ($model->getMeasurement('neckCircumference')/3.1415) / 2 + 5;
        $collarDepth = $this->help['collarShapeFactor'] * (
                $model->getMeasurement('neckCircumference') 
                + $this->getOption('collarEase')
            ) / 5 - 8;
        
        $p = $this->parts['base'];
        $p->setTitle('Base');

        // Center vertical axis
        $p->newPoint( 1 ,   0,  $this->getOption('backNeckCutout'), 'Center back @ neck');
        $p->newPoint( 2 ,   0,  $p->y(1) + $this->help['armholeDepth'], 'Center back @ armhole depth' );
        $p->newPoint( 3 ,   0,  $p->y(1) + $model->getMeasurement('centerBackNeckToWaist'), 'Center back @ waist' );
        $p->newPoint( 4 ,   0,  $model->getMeasurement('centerBackNeckToWaist') + $model->getMeasurement('naturalWaistToTrouserWaist') + $this->getOption('backNeckCutout') , 'Center back @ trouser waist');
        
        // Side vertical axis
        $p->newPoint( 5 , $model->getMeasurement('chestCircumference')/4 + $this->getOption('chestEase')/4, $p->y(2) , 'Quarter chest @ armhole depth' );
        $p->newPoint( 6 , $p->x(5), $p->y(4), 'Quarter chest @ trouser waist' );
        
        // Back collar
        $p->newPoint( 7 , $collarWidth, $p->y(1) , 'Half collar width @ center back' );
        $p->newPoint( 8 , $p->x(7), $p->y(1) - $this->getOption('backNeckCutout'), 'Half collar width @ top of garment' );
        
        // Front collar
        $p->newPoint( 9 , 0, $p->y(1) + $collarDepth, 'Center front collar depth');

        // Armhole
        $p->newPoint( 10 , $model->getMeasurement('acrossBack')/2, $p->y(1) + $p->deltaY(1,2)/2, 'Armhole pitch point' );
        $p->newPoint( 11 , $p->x(10), $p->y(2) , 'Armhole pitch width @ armhole depth');
        $p->newPoint( 12 , $p->x(7) + sqrt(pow($model->getMeasurement('shoulderLength'),2) - pow($model->getMeasurement('shoulderSlope')/2,2)), $model->getMeasurement('shoulderSlope')/2, 'Shoulder tip' );
        $p->addPoint( 13 , $p->Shift(5, 180, $p->distance(11,5)/4) , 'Left curve control point for 5');
        $p->addPoint( '.help1' , $p->shift(11, 45, 5), '45 degrees upwards' );
        $p->addPoint( '.help2' , $p->linesCross(11, '.help1', 5, 10), 'Intersection');
        $p->addPoint( 14 , $p->shiftTowards(11, '.help2', $p->distance(11, '.help2')/2), 'Point on armhole curve');
        $p->addPoint( 15 , $p->shift(14, 135, 25), 'Top curve control point for 14' );
        $p->addPoint( 16 , $p->Shift(14, -45, 25), 'Bottom control point for 14' );
        $tmp =  $p->deltaY(12,10)/3;
        $p->addPoint( 17 , $p->shift(10, 90, $tmp), 'Top curve control point for 10' );
        $p->addPoint( 18 , $p->shift(10, -90, $tmp), 'Bottom curve control point for 10');
        $p->addPoint( 19 , $p->shift(12, $p->angle(8,12)+90, 10), 'Bottom control point for 12' );

        // Control point for collar
        $p->addPoint( 20 , $p->shift(8, $p->angle(8,12)+90, $this->getOption('backNeckCutout')), 'Curvei control point for collar' );

        // Paths
        $path = 'M 1 L 2 L 3 L 4 L 6 L 5 C 13 16 14 C 15 18 10 C 17 19 12 L 8 C 20 1 1 z';
        $p->newPath('outline', $path);
        $path = 'M 9 C 20 20 8';
        $p->newPath('vNeck', $path);

        // Title anchor
        $p->newPoint('titleAnchor', $p->x(10)/2, $p->y(10), 'Title anchor');
        $attr = ['id' => "base-title", 'class' => 'title'];
        $p->newText('title', $p->points['titleAnchor'], $p->title, $attr);
        
        // Seam allowance
        $p->offsetPath('sa', 'outline');
}

    private function draftBody($model) 
    {
        $this->clonePoints('base', 'body');
        
        $p = $this->parts['body'];
       
        $mirrorThese = array(1,2,3,5,6,7,9,10,11,12,13,14,15,16,17,18,101,121,122,161,171,181,182,200,501);
        foreach($mirrorThese as $key) {
            $p->addPoint(-1*$key, $p->flipX($key, $p->x(5)) );
        }
        $p->newPoint( 201, $p->x(5), $p->y(200) );
        $frontextra = 5; // Cutting out armhole a bit deeper at the front
        $p->addPoint( -12 , $p->shift(-12, 0, $frontextra) );
        $p->addPoint( -121 , $p->shift(-121, 0, $frontextra) );
        $p->addPoint( -122 , $p->shift(-122, 0, $frontextra) );
        // Need to figure out what curve the back pitch point will intersect with
        $backArmPitchY = $p->y(15) - 110; // FIXME, need to check why 110 was used here
        if($backArmPitchY == $p->y(12) ) { // Smack between curves
            $p->newPoint( 20, $p->x(12), $p->y(12), 'Back pitch point'); 
        } 
        else if($backArmPitchY < $p->y(12) ) {
            $p->addPoint( 20, $p->curveCrossesY(12, 121, 161, 16, $backArmPitchY) ); // Top curve
        }
        else if($backArmPitchY > $p->y(12) ) {
            $p->addPoint( 20, $p->curveCrossesY(18, 181, 122, 12, $backArmPitchY) ); // Bottom curve
        }
        $p->addPoint( 2066601, $p->shift(20,90,2.5), 'Back pitch point Top Notch');
        $p->addPoint( 2066602, $p->shift(20,-90,2.5), 'Back pitch point Bottom Notch');
        // Need to figure out what curve the front pitch point will intersect with
        $frontarmpitchY = $p->y(-15) - 80; // FIXME, need to check why 80 was used here
        if($frontarmpitchY == $p->y(-12) ) {
            $p->newPoint( -20, $p->x(-12), $p->y(-12), 'Front pitch point'); // Smack between curves
        }
        else if($frontarmpitchY < $p->y(-12) ) {
            $p->addPoint( -20, $p->curveCrossesY(-12, -121, -161, -16, $frontarmpitchY) ); // Top curve
        }
        else if($frontarmpitchY > $p->y(-12) ) {
            $p->addPoint( -20, $p->curveCrossesY(-18, -181, -122, -12, $frontarmpitchY) ); // Bottom curve
        }
    }
    
    private function finalizeBody($model) 
    {
        $p = $this->parts['body'];
        
        $frontPath = 'M 200 L 201 L 5 C 501 182 18 C 181 122 12 C 121 161 16 L 10 C 10 101 1 z';
        $backPath = 'M -17 L -200 L 201 L -5 C -501 -182 -18 C -181 -122 -12 C -121 -161 -16 L -10 C -9 -171 -17 z';
        $pathOptions = ['class' => 'cutline'];
        
        $p->newPath('front', $frontPath, $pathOptions);
        $p->newPath('back', $backPath, $pathOptions);
        
        $p->newPoint( 'titleAnchor', $p->x(9), $p->y(12) ); 
        $attr = ['id' => "body-title", 'class' => 'title'];
        $p->newText('title', $p->points['titleAnchor'], $p->title, $attr);
        
        $p->newSnippet('scalebox', 'scalebox', $p->points['titleAnchor']);
        $p->newPoint( 'logoAnchor', $p->x(9), $p->y(12)+200 ); 
        $p->newSnippet('logo', 'logo', $p->points['logoAnchor']);
    }

    private function draftSleeve($model) 
    {
        $p = $this->parts['sleeve'];
        
        $aseam = ($this->armholeLen() + $this->getOption('sleevecapEase')) * $this->help['sleevecapShapeFactor'];
        
        $p->newPoint( 1 , 0, 0 );
        $p->newPoint( 2 , $p->x(1), $model->getMeasurement('sleeveLengthToWrist') );
        $p->newPoint( 3 , $model->getMeasurement('upperBicepsCircumference') + $this->getOption('bicepsEase'), 0 ); 
        $p->newPoint( 2030 , $p->x(3), $p->y(2) );
        $p->newPoint( 4 , $p->x(3)/2, 0 ); 
        $p->newPoint( 401 , $p->x(3)/4, 0); 
        $p->newPoint( 402 , $p->x(3)*0.75, 0 ); 
        $p->newPoint( 5 , $p->x(1), $aseam/3 ); 
        $p->newPoint( 6 , $p->x(3), $p->y(5) ); 
        $p->newPoint( 7 , $p->x(401), $p->y(5) ); 
        $p->newPoint( 8 , $p->x(402), $p->y(5) ); 
        $p->newPoint( 50 , $p->x(4), $p->y(5) ); 
        $p->newPoint( 51 , $p->x(4)-50, $p->y(50)+50 ); 
        $p->newPoint( 52 , $p->x(51), $p->y(51)+70 ); 
        $p->newPoint( 701 , $p->x(7), $aseam/6 - 15, 'Back Pitch Point');
        // I am moving this 5mm out for some extra room
        $p->newPoint( 801 , $p->x(8) + 5, $aseam/6, 'Front Pitch Point');
        // Angles of the segments of the sleevecap
        $angle5701 = $p->angle(5,701);
        $angle4701 = $p->angle(4,701);
        $angle6801 = $p->angle(6,801);
        $angle4801 = $p->angle(4,801);
        $p->addPoint( 5701 , $p->shiftTowards(5,701, $p->distance(5,701)/2) );
        $p->addPoint( 57011 , $p->shift(5701, $angle5701+90, 5) );
        $p->addPoint( 4701 , $p->shiftTowards(4, 701, $p->distance(4,701)/2) );
        $p->addPoint( 47011 , $p->shift(4701, $angle4701+90, 15) );
        $p->addPoint( 6801 , $p->shiftTowards(6, 801, $p->distance(6,801)/2) );
        $p->addPoint( 68011 , $p->shift(6801, $angle6801-90, 15) );
        $p->addPoint( 4801 , $p->shiftTowards(4, 801, $p->distance(4,801)/2) );
        $p->addPoint( 48011 , $p->shift(4801, $angle4801-90, 23) );

        $p->addPoint( 57012 , $p->shift(57011, $angle5701,  25) );
        $p->addPoint( 57013 , $p->shift(57011, $angle5701, -25) );
        $p->addPoint( 47012 , $p->shift(47011, $angle4701,  25) );
        $p->addPoint( 47013 , $p->shift(47011, $angle4701, -25) );
        $p->addPoint( 68012 , $p->shift(68011, $angle6801,  25) );
        $p->addPoint( 68013 , $p->shift(68011, $angle6801, -25) );
        $p->addPoint( 48012 , $p->shift(48011, $angle4801,  25) );
        $p->addPoint( 48013 , $p->shift(48011, $angle4801, -25) );
 
        $p->addPoint( 41 , $p->shift(4, 0, -25) );
        $p->addPoint( 42 , $p->shift(4, 0, 25) );
        $p->addPoint( 43 , $p->shiftAlong(4, 42, 48012, 48011, 5) );

        $p->newPoint( 11 , $p->x(1), $p->y(5) + $p->distance(2,5)/2 - 25, 'Elbow point');
        $p->newPoint( 12 , $p->x(3), $p->y(11) );
        $width = $model->getMeasurement('wristCircumference') + $this->getOption('cuffEase');
        $p->newPoint( 21 , $p->x(4) - $width/2, $p->y(2) );
        $p->addPoint( 22 , $p->flipX(21,$p->x(4)) );
        $p->addPoint( 23 , $p->linesCross(11, 12, 5, 21) );
        $p->addPoint( 24 , $p->linesCross(11, 12, 6, 22) );
        
    }
    
    private function finalizeSleeve($model) 
    {
        $p = $this->parts['sleeve'];
        
        $sleevePath = "M 5 C 5 57012 57011 C 57013 701 701 C 701 47013 47011 C 47012 41 4 C 42 48012 48011 C 48013 801 801 C 801 68013 68011 C 68012 6 6 L 22 L 21 z";
        $pathOptions = ['class' => 'cutline'];
        
        $p->newPath('sleeve', $sleevePath, $pathOptions);
        
        $p->newPoint( 'titleAnchor', $p->x(4), $p->y(5) ); 
        $attr = ['id' => "body-title", 'class' => 'title'];
        $p->newText('title', $p->points['titleAnchor'], $p->title, $attr);
    }
    
    private function tweakCollar($model) {
        $this->help['collarIteration'] = 1;
        $this->checkCollarDelta($model);
        $this->log('      Calculating collar opening with '.$this->getOption('collarEase').'mm collar ease.');
        while(abs($this->help['collarDelta']) > 0.5 && $this->help['collarIteration'] < 150) {
            $this->log("      Iteration ".$this->help['collarIteration'].", collar opening length is ".$this->help['collarDelta']." mm off");
            $this->fitCollar($model);
            $this->checkCollarDelta($model);
        }
        if($this->help['collarIteration']>149) $this->log("      Iteration ".$this->help['collarIteration'].", collar opening length is ".$this->help['collarDelta']." mm off. I'm not happy, but it will have to do.");
        else $this->log("      Iteration ".$this->help['collarIteration']." collar opening length is ".$this->help['collarDelta']." mm off. I'm happy.");
    }

    private function fitCollar($model) 
    {
        $this->help['collarIteration']++;
        if($this->help['collarDelta'] > 0) $this->help['collarShapeFactor'] = $this->help['collarShapeFactor'] * 0.97; 
        else $this->help['collarShapeFactor'] = $this->help['collarShapeFactor'] * 1.03; 
        $this->draftBase($model);
        $this->draftBody($model);
    }
    
    private function tweakSleeve($model) 
    {
        $this->help['sleeveIteration'] = 1;
        $this->checkSleevecapDelta();
        $this->log('      Calculating sleevecap opening with '.$this->getOption('sleevecapEase').'mm sleevecap ease.');
        while(abs($this->help['sleevecapDelta'])>0.5 && $this->help['sleeveIteration'] < 150) {
            $this->log("      Iteration ".$this->help['sleeveIteration'].", sleevecap length is ".$this->help['sleevecapDelta']." mm off");
            $this->fitSleeve($model);
            $this->checkSleevecapDelta();
        }
        if($this->help['sleeveIteration']>149) $this->log("      Iteration ".$this->help['sleeveIteration'].", sleevecap length is ".$this->help['sleevecapDelta']." mm off. I'm not happy, but it will have to do.");
        else $this->log("      Iteration ".$this->help['sleeveIteration']." collar opening length is ".$this->help['sleevecapDelta']." mm off. I'm happy.");
    }


    private function fitSleeve($model) 
    {
        $this->help['sleeveIteration']++;
        if($this->help['sleeveIteration'] > 18) {
            if($this->help['sleevecapDelta'] > 0) $this->help['sleevecapShapeFactor'] = $this->help['sleevecapShapeFactor'] * 0.9; 
            else $this->help['sleevecapShapeFactor'] = $this->help['sleevecapShapeFactor'] * 1.1; 
        }
        else if($this->help['sleeveIteration'] > 10) {
            if($this->help['sleevecapDelta'] > 0) $this->help['sleevecapShapeFactor'] = $this->help['sleevecapShapeFactor'] * 0.95; 
            else $this->help['sleevecapShapeFactor'] = $this->help['sleevecapShapeFactor'] * 1.05; 
        } 
        else {
            if($this->help['sleevecapDelta'] > 0) $this->help['sleevecapShapeFactor'] = $this->help['sleevecapShapeFactor'] * 0.99; 
            else $this->help['sleevecapShapeFactor'] = $this->help['sleevecapShapeFactor'] * 1.01; 
        }
        $this->draftSleeve($model);
    }

    private function sleevecapLen() 
    {
        $sleeve = $this->parts['sleeve'];
    
        return  
            $sleeve->curveLen(5, 5, 57012, 57011) + 
            $sleeve->curveLen(57011, 57013, 701, 701) + 
            $sleeve->curveLen(701, 701, 47013, 47011) + 
            $sleeve->curveLen(47011, 47012, 41, 4) +
            $sleeve->curveLen(4, 42, 48012, 48011) +
            $sleeve->curveLen(48011, 48013, 801, 801) +
            $sleeve->curveLen(801, 801, 68013, 68011) +
            $sleeve->curveLen(68011, 68012, 6, 6); 
    }

    private function armholeLen() 
    {
        $body = $this->parts['body'];
   
        return (
            $body->curveLen(16, 161, 121, 12) + 
            $body->curveLen(12, 122, 181, 18) + 
            $body->curveLen(18, 182, 501, 5) 
        ) * 2; 
    }
    
    private function collarLen() 
    {
        $body = $this->parts['body'];
        
        return (
            $body->curveLen(10, 9, 171, 17) + 
            $body->curveLen(1, 101, 10, 10)  
        ) * 2;
    }

    private function checkSleevecapDelta() 
    {
        $this->help['sleevecapDelta'] = round($this->sleevecapLen() - ($this->armholeLen() + $this->getOption('sleevecapEase')), 2);
    }
    
    private function checkCollarDelta($model) 
    {
        $this->help['collarDelta'] = round($this->collarLen() - ($model->getMeasurement('neckCircumference') + $this->getOption('collarEase')), 2);
    }

    private function log($msg)
    {
        if(is_array($msg)) $msg = print_r($msg,1);
        $this->help['log'][] = $msg;
    }
}