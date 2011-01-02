<?php

$f = file_get_contents( "ajax-loader.gif" );

echo chunk_split( base64_encode( $f ));
