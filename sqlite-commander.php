<?php

/* SQLite Commander
 *
 * usage
 * =====
 * add to .htaccess:
 * 	RewriteEngine on
 * 	RewriteRule ^(.*(db|sqlite[3]?))c?$ /.sqlite3commander.php?file=$1
 */

$TIME = array(
	'start' => microtime( true )
);

$file = false;
$URI = isset( $_SERVER['REQUEST_URI'] )?$_SERVER['REQUEST_URI']:false;

classes:

class SQLiteCommander {
	const name = 'SQLiteCommander';
	const version = '0.0.5';
	public static $authors = array(
		'Mateusz Adamowski' => 'http://mateusz.adamowski.pl'
	);
	const license = 'public domain';
}

class DB {
	public static $file;
	private static $instance = null;
	public static $ver;
	private $pdo;
	private function __construct(){
		$str = fgets( fopen( self::$file, 'r' ), 80 );
		self::$ver = strpos( $str, 'This file contains an SQLite 2.1 database **' )
			? 'sqlite2'
			: ( strpos( $str, 'QLite format 3' ) 
				? 'sqlite'
				: false
			);
		$this->pdo = new PDO( self::$ver . ":" . self::$file );
	}
	private function __getInstance(){
		if ( null === self::$instance ){
			self::$instance = new self;
		}
		return self::$instance;
	}
	public static function query( $q ){
		return self::__getInstance()->pdo->query( $q );
	}
	public static function exec( $q ){
		return self::__getInstance()->pdo->exec( $q );
	}
	public static function escape( $q ){
		return self::__getInstance()->pdo->quote( $q );
	}
	public static function errorCode(){
		return self::__getInstance()->pdo->errorCode();
	}
	public static function errorInfo(){
		return self::__getInstance()->pdo->errorInfo();
	}
}

class Table {
	public $cols = array();
	public $count;
	public $name;
	public function __construct( $n ){
		$this->name = $n;
		$wyniki = DB::query( "
			SELECT t.*
			FROM ( SELECT null )
			LEFT JOIN \"{$this->name}\" t
			LIMIT 1
		" )->fetchAll();
		foreach( $wyniki[0] as $colname => $v ){
			if ( is_numeric( $colname )) continue;
			$this->cols[] = $colname;
		}
		$c = DB::query( "
			SELECT count(*) AS count
			FROM \"{$this->name}\"
		" )->fetchAll();
		$this->count = (int) $c[0]['count'];
	}
}

userinput:
	$file = isset( $_GET['file'] ) ? $_GET['file'] : false;
	$query = isset( $_POST['query'] ) ? $_POST['query'] : false;
	$type = isset( $_POST['type'] ) ? $_POST['type'] : false;

bootstrap:
	session_start();
	DB::$file = $file;

dispatcher:
	$req = isset( $_SERVER['REQUEST_METHOD'] ) ? $_SERVER['REQUEST_METHOD'] : false;
	switch( $req ){
	case 'GET':
		goto main;
	case 'POST':
		switch( $type ){
		default:
		case 'query' : goto ajax_query;
		case 'info' : goto ajax_info;
		}
	}
main:
	goto response_main;

ajax_query:
	$TIME['startQuery'] = microtime( true );
	if ( substr( $query, 0, 2 ) === 'eJ' ){
		$query = gzuncompress( base64_decode( $query ));
	}
	$wyniki = DB::query( $query );
	if ( false !== $wyniki ){
		$dane = $wyniki->fetchAll( PDO::FETCH_CLASS );
	}
	$TIME['endQuery'] = microtime( true );
	goto response_ajax_query;

ajax_info:
	switch( $query ){
	case 'tables':
		$q = DB::query( "
			SELECT name
			FROM sqlite_master
			WHERE type = 'table'
			ORDER BY name
		");
		if ( false !== $q ){
			$tables = $q->fetchAll( PDO::FETCH_CLASS );
		}
		break;
	default:
		
	}
	$TIME['end'] = microtime( true );
	goto response_ajax_info;

response_main: 
resources: 

$IMG['loader'] = <<<END
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
END;

?>
<? header('Content-Type: text/html; charset=UTF-8') ?>
<!doctype html>
<html>
<head>
<meta http-equiv='Content-Type' content='text/html; charset=UTF-8' />
<title><?= SQLiteCommander::name ?> v.<?= SQLiteCommander::version ?></title>
<style type='text/css'>
body {
	margin: 0; padding: 0;
	background-color: #fff;
}

div#top h1 {
	color: #fff;
	text-shadow: #000 1px 1px 2px, #000 0px 0px 3px;
	font-family: Tahoma, sans-serif;
	margin: 0;
	padding: 5px;
	font-size: 12pt;
}

div#top {
	border: 0;
	position: fixed;
	background-color: #cf0;
	top: 0; left: 0;
	font-family: Tahoma, sans-serif;
	color: #fff;
	padding: 10px;
	width: 100%;
}

div#console {
	margin-top: 80px;
	padding: 5px;
	font-family: "DejaVu Sans Mono", monospace;
	padding: 10px;
}

div#console div#info {
	font-family: Tahoma, sans-serif;
}

div#aside {
	width: 200px;
	background-color: #464;
	float: left;
}

div.error {
	border: 5px solid #d66;
	background-color: #fdd;
	padding: 40px;
	color: #000;
}

div.result { 
	font-size: 10pt;
}

div.result table td {
	padding: 2px;
	background-color: #ef8;
}

div.result td.number { 
	text-align: right;
}

div.result code.null {
	color: #181;
	font-style: italic;
}

form input#query {
	padding: 10px;
	border: 1px solid #000;
	background-color: #ef8;
	font-family: monospace;
	font-weight: bold;
}

form input#query:active {
	background-color: #ffd;
}

input::-webkit-input-placeholder {
	font-family: Tahoma, sans-serif;
	font-style: italic;
	color: #460;
	font-weight: normal;
}
</style>
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/prototype/1.7.0.0/prototype.js"></script>
<script type="text/javascript">
/* <![CDATA[ */
var SQLiteCommander = {
	init : function(){
		if ( top.location.hash.length > 1 ){
			$('query').value = decodeURIComponent( top.location.hash.substr(1) )
			SQLiteCommander.send_query();
		}
	},
	send_query : function(){
		var a = new Ajax.Request(
			'<?= $URI ?>', {
				onSuccess: this.parse_response,
				onFailure: function( res ){
					alert('Blad komunikacji')
				},
				parameters: {
					type: 'query',
					query: $('query').value
				}
			}
		)
	},

	parse_response : function( response ){
		var r = response.responseJSON
		var type
		if ( false === r.results ){
			type = 'error'
		}
		else {
//			top.location.hash = '#' + encodeURIComponent( $('query').value )
			top.location.hash = '#' + encodeURIComponent( (r.sqlgz.length < r.sql.length) ? r.sqlgz : r.sql )
			$('query').value = r.sql
			type = ( 0 === r.columncount ) ? 'nodata' : 'data'
		}
		r.bytes = response.responseText.length
		$('console').innerHTML = SQLiteCommander.show(type, r )
	},
	show : function( type, response_data ){
		return {
			'error' : function( r ){ return [ 
				'<div class="error">', r.error[2], '</div>'
				] },
			'nodata' : function( r ){ return [
				'<div>', 'affected rows: ', r.affected, '</div>'
				] },
			'data' : function( r ){ return [
				'<div class="result">',
				'<table>', 
				'<caption>', 'set: ', r.rowcount, 'x', r.columncount, '<br />', '</caption>',
				r.results.length > 0 
					? Object.keys( r.results[0] ).
					collect( function( h ){ return h.replace(/&/g, "&amp;").replace(/>/g, "&gt;").replace(/</g, "&lt;"); } ).
					collect( function( header ){ return [ '<th>', header ].join('') } ).
					join('')
					: '<tr><td>&nbsp;</td></tr>',
				r.results.collect( function(row){ return [ 
						'<tr>', 
						Object.values( row ).collect( function( h ){ return h===null
							? '<code class="null">null</code>'
							: h.replace(/&/g,"&amp;").replace(/</g,'&lt;').replace(/>/g,'&gt;')
							} ).
						collect( function( cell ){ return [ 
							'<td class="',
							/^[-+]?[0-9]*\.?[0-9]*$/.test( cell ) ? 'number' : 'text' , '">',
							cell, '</td>'
						] } )
					] }
				),
				'</table></div>',
				'<dl><dt>prepare time</dt>',
				'<dd>', (r.time.startQuery - r.time.start)*1000, ' ms</dd></dl>',
				'<dl><dt>query time</dt>',
				'<dd>', (r.time.endQuery - r.time.startQuery)*1000, ' ms</dd></dl>',
				'<dl><dt>response string size:</dt>',
				'<dd>', r.bytes, ' bytes</dd></dl>',
				'</dl>'
				] } 
		}[type](response_data).flatten().join('')
	} 
}
/* ]]> */
</script>
</head>
<body onload='SQLiteCommander.init()'>
<div id='top'>
<h1><?= SQLiteCommander::name ?></h1>
<form action='<?= $URI ?>' method='POST' onsubmit='SQLiteCommander.send_query(); return false'>
<input type='text' id='query' name='query' size='100' value='' placeholder='insert SQL statement here...' />
<input type='button' value='test' onclick='$("query").value="SELECT *, &apos;<u>qwe</u>&apos; FROM sqlite_master"; SQLiteCommander.send_query();' />
<input type='button' value='test' onclick='$("query").value="SELECT * FROM zawodnicy"; SQLiteCommander.send_query();' />
<img src='data:image.gif;base64,<?= $IMG['loader'] ?>' />
</form>
</div>
<div id='aside'>
<dl>
<dt>test</dt>
<dd>asdd</dd>
<dt>test</dt>
<dd>asdd</dd>
<dt>test</dt>
<dd>asdd</dd>
<dt>test</dt>
<dd>asdd</dd>
</dl>
</div>
<div id='console'>
<div id='info'>
<h1>Welcome to <?= SQLiteCommander::name ?></h1>
<h2>Version</h2>
<p><?= SQLiteCommander::version ?></p>
<h2>Authors:</h2>
<ul>
<? foreach ( SQLiteCommander::$authors as $author => $web ): ?>
<li><p><a href='<?= $web ?>'><?= $author ?></a></p></li>
<? endforeach ?>
</ul>
</div>
</div>
</body>
</html>

<? exit ?>

<? response_ajax_query: ?>
<? header('Content-Type: application/json') ?>
<? if ( false !== $wyniki ): ?>
<?= json_encode( array( 
	'results' => $dane,
	'sql' => $query,
	'sqlgz' => base64_encode( gzcompress( $query )),
	'rowcount' => count( $dane ),
	'columncount' => $wyniki->columnCount(),
	'affected' => $wyniki->rowCount(),
	'time' => $TIME
) ) ?>
<? else : ?>
<?= json_encode( array( 
	'results' => false,
	'error' => DB::errorInfo(),
	'time' => $TIME
) ) ?>
<? endif ?>
<? exit ?>

<? response_ajax_info: ?>
<? header('Content-Type: application/json') ?>
<?= json_encode( array(
	'tables' => array(
		array( 'name' => 'LOL' ),
		array( 'name' => 'QWE' )
	)
) ) ?>
<? exit ?>

