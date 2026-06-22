<?php
$nbcircles=15;
$minsize=1.3;
$maxsize=4;
$r=20;
$TeinteFrom=[45,63,80];
$TeinteTo=[26,189,157];
for($i=0; $i<$nbcircles; $i++) {
	$teinteR=$TeinteFrom[0]*(1-$i/$nbcircles)+$TeinteTo[0]*$i/$nbcircles;
	$teinteV=$TeinteFrom[1]*(1-$i/$nbcircles)+$TeinteTo[1]*$i/$nbcircles;
	$teinteB=$TeinteFrom[2]*(1-$i/$nbcircles)+$TeinteTo[2]*$i/$nbcircles;
	echo "<circle cx=\"".(24+20*cos($i*6.283/$nbcircles))."\" cy=\"".(24+20*sin($i*6.283/$nbcircles))."\" r=\"".($minsize*(1-$i/$nbcircles)+$maxsize*$i/$nbcircles)."\" fill=\"rgb(".$teinteR.",".$teinteV.",".$teinteB.")\"/>\n";
}
?>