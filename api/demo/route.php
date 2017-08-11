<?php

$array_route[] = array('title'=>'แก่งกระจาน'
					,'abstact'=>'ไปชมหมอก กอดเขา โดดน้ำให้ฉ่ำใจ ที่แก่งกระจาน 3 วัน 2 คืน'
					,'cover_url'=>'http://www.chillpainai.com/src/wewakeup/scoop/scoop_hilight/7745.jpg?v=1001'
					,'author'=>'John Doe'
					,'like'=>rand(10,300)
					,'favourite'=>rand(10,100));

$array_route[] = array('title'=>'นนทบุรี'
					,'abstact'=>'คิดถึงเมืองนนท์ เดินเล่นกินของอร่อยท่าน้ำนนท์'
					,'cover_url'=>'http://www.chillpainai.com/src/wewakeup/scoop/scoop_hilight/7607.jpg?v=1001'
					,'author'=>'John Doe'
					,'like'=>rand(10,300)
					,'favourite'=>rand(10,100));

$array_route[] = array('title'=>'แม่แจ่ม'
					,'abstact'=>'อำเภอแม่แจ่ม เป็นเมืองเล็กๆ ที่อยู่ท่ามกลางหุบเขาน้อยใหญ่ ชาวบ้านที่นี่นั้นใช้ชีวิตแบบเรียบง่าย เงียบสงบ '
					,'author'=>'John Doe'
					,'cover_url'=>'http://www.chillpainai.com/src/wewakeup/hilight_travel/758.jpg'
					,'like'=>rand(10,300)
					,'favourite'=>rand(10,100));

$array_route[] = array('title'=>'แกรนแคนยอนเชียงใหม่'
					,'abstact'=>'แกรนแคนยอนเชียงใหม่ อำเภอหางดง ที่เที่ยวสุดฮิตของวัยรุ่นและชาวต่างชาติ ณ ขณะนี้ '
					,'author'=>'John Doe'
					,'cover_url'=>'http://www.chillpainai.com/src/wewakeup/img_travel/759/1453954145-_MG_9216.jpg'
					,'like'=>rand(10,300)
					,'favourite'=>rand(10,100));

$array_route[] = array('title'=>'ห้วยแม่ขมิ้น'
					,'abstact'=>'น้ำตกห้วยแม่ขมิ้น ตั้งอยู่บริเวณที่ทำการอุทยานแห่งชาติศรนครินทร์ ริมทะเลสาบเขื่อนศรีนครินทร์'
					,'author'=>'John Doe'
					,'cover_url'=>'http://www.chillpainai.com/src/wewakeup/hilight_travel/738.jpg'
					,'like'=>rand(10,300)
					,'favourite'=>rand(10,100));


echo json_encode($array_route);

?>