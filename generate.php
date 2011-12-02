#!/usr/bin/php
<?php

// Get the report to generate
$report = $argv[1].'/';
$output = "${report}output/";

// overrite output dir with root if not exists
if (is_dir($output)) {
  `rm -rf $output`;
}
mkdir($output);
`cp -R ${report}root/* $output`;
unlink($output.'template.html');
if (file_exists($output.'nav.html')) {
unlink($output.'nav.html');
}
`rm -f $output/template*`;

// get the pages to generate
$pages = file("$report/pages.txt");

echo 'Outputing files...'. PHP_EOL;
foreach ($pages as $count => $page) {
  $pagedata = explode('|',$page);
  $title = trim($pagedata[0]);
  $file = $output.trim($pagedata[1]).'.html';
  if (file_exists($report.'root/template-'.trim($pagedata[1]).'.html')) {
    copy($report.'root/template-'.trim($pagedata[1]).'.html', $file);
  } else {
    copy($report.'root/template.html', $file);
  }
  $nav = generatenav($pages, trim($pagedata[1]));
  $prev = $count > 0 ? pageinfo($pages[$count-1]): array('','index');
  $next = $count < count($pages) -1?pageinfo($pages[$count+1]): array('','index');
  $inc = 1;
  while (true) {
    if ($next[2] == true) {
      $next = pageinfo($pages[$count+$inc]);
      $inc++;
    } else {
      break;
    }
  }
  $inc = 1;
  while (true) {
    if ($prev[2] == true) {
      $prev = pageinfo($pages[$count-$inc]);
      $inc++;
    } else {
      break;
    }
  }
  $current = pageinfo($pages[$count]);
  if ($prev[1] == $current[1] && $count != 0) {
    $prev = pageinfo($pages[$count-2]);
  }
  $filecontents = file_get_contents($file);
  $newcontents = str_replace('{navigation}', implode(PHP_EOL, $nav), $filecontents);
  $newcontents = str_replace('{previous}', $prev[1].'.html', $newcontents);
  $newcontents = str_replace('{next}', $next[1].'.html', $newcontents);
  $newcontents = str_replace('{page}', str_replace('&nbsp;',' ',strip_tags(trim($pagedata[0]))), $newcontents);
  if (file_exists($report.'pages/'.$current[1].'.html')) {
    $newcontents = str_replace('{content}', file_get_contents($report.'pages/'.$current[1].'.html'), $newcontents);
    echo '--- ';
  } else {
    if (trim(@$pagedata[2]) == 'pdf') {
        echo 'pdf ';
    } else {
        echo 'xxx ';
    }
  }
  file_put_contents($file, $newcontents);
  if (trim(@$pagedata[2]) == 'pdf') {
    unlink ($file);
    echo str_replace('html','pdf',$file). PHP_EOL;
  } else {
    echo $file . PHP_EOL;
  }
}

exit(0);

function pageinfo($record) {
  $pagedata = explode('|',$record);
  $title = trim($pagedata[0]);
  $base = trim($pagedata[1]);
  $pdf = isset($pagedata[2])? true: false;
  return array($title, $base, $pdf);
}

function generatenav($pages, $curfile) {
  $pages = array_reverse($pages);
  $li = array();
  $sub = array();
  $subhighlight = false;
  foreach ($pages as $page) {
    $pagedata = explode('|',$page);
    $title = trim($pagedata[0]);
    $file = trim($pagedata[1]).'.html';
    $highlight = ($curfile == trim($pagedata[1])) ? '<span style="color: #ED2823">%s</span>' : '%s';
    if (substr($pagedata[0], 0, 1) == ' ') {
    /*
     * If this is a submenu
     */
      $class = count($sub) == 0 ? ' class="last"' : '';
      array_unshift($sub, '<li'.$class.'><a href="'.$file.'">'.sprintf($highlight,'&ndash; '.$title).'</a></li>');
      if ($curfile == trim($pagedata[1])) {
        $subhighlight = true;
      }
    } else {
    /*
     * If this is a normal menu
     */
      if (count($sub)>0 && $subhighlight) {
        $subnav = '<ul>'.implode($sub).'</ul>';
        if ($subhighlight) {
            $highlight = '<span style="color:#ED2823">%s</span>';
            $subhighlight = false;
        }
        array_unshift($li, '<li class="sub"><a href="'.$file.'">'.sprintf($highlight, $title).'</a>'.$subnav.'</li>');
        $sub = array();
      } else {
        switch (trim($pagedata[1])) {
        case 'downloads':
            $class = ' class="nav_download"';
            $img = '<img class="aricons" width="12" height="10" alt="" src="assets/images/site/s.gif">';
            break;
        case 'contact-us':
            $class = ' class="nav_contact_us"';
            $img = '<img class="aricons" width="12" height="10" alt="" src="assets/images/site/s.gif">';
            break;
        default:
            $class = '';
            $img = '';
        }
        switch (trim(@$pagedata[2])) {
        case 'pdf':
            $class = '';
            $img = '<img class="aricons" width="12" height="10" alt="" src="assets/images/site/s.gif">';
            $file = trim($pagedata[1]).'.pdf';
            break;
        }
        array_unshift($li, '<li><a'.$class.' href="'.$file.'">'.$img.sprintf($highlight, $title).'</a></li>');
        $sub = array();
      }
    }
  }
  return $li;
}
