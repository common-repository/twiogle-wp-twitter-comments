<?php
/*
Plugin Name: Twiogle Twitter Commenter
Plugin URI: http://twiogle.com
Description: Pull comments from Twitter based off of your tags in your posts.
Version: 1.3
Author: Twiogle
Author URI: http://twiogle.com
*/

include('twitterClass.php');

add_action('admin_menu', 'add_twiogle_commenter_menu');
register_activation_hook(__FILE__,'twiogle_commenter_install');


function add_twiogle_commenter_menu()
{
add_options_page('Twiogle Twitter Commenter', 'Twiogle Twitter Commenter', 8, 'twioglecommenter', 'twiogle_comment_options_page');

}

register_activation_hook(__FILE__, 'twiogle_comments_activation');
add_action('twiogle_find_and_post_comments', 'doComments');

function twiogle_comments_activation() {
	wp_schedule_event(time(), 'twicedaily', 'twiogle_find_and_post_comments');
}



register_deactivation_hook(__FILE__, 'twiogle_comments_deactivation');
function twiogle_comments_deactivation() {
	wp_clear_scheduled_hook('twiogle_find_and_post_comments');
}


function twiogle_comment_options_page()
{

//testing wordpress querys





    // variables for the field and option names 
    $opt_name = 'mt_posts_per_page';
    $hidden_field_name = 'mt_submit_hidden';
    $data_field_name = 'mt_posts_per_page';

    $opt_name2 = 'twiogle_make_no_follow';
    $hidden_field_name2 = 'twiogle_make_no_follow_hidden';
    $data_field_name2 = 'twiogle_make_no_follow';

    
    $opt_name3 = 'link_back_to_profile';
    $hidden_field_name3 = 'link_back_to_profile_hidden';
    $data_field_name3 = 'link_back_to_profile';

    $opt_name4 = 'remove_hashtags';
    $hidden_field_name4 = 'remove_hashtags_hidden';
    $data_field_name4 = 'remove_hashtags';


    // Read in existing option value from database
    $maxComments = get_option( $opt_name );
    $maxComments2 = get_option( $opt_name2 );
    $maxComments3 = get_option( $opt_name3 );
    $maxComments4 = get_option( $opt_name4 );

    // See if the user has posted us some information
    // If they did, this hidden field will be set to 'Y'
    if( $_POST[ $hidden_field_name ] == 'Y' ) {
        // Read their posted value
        $maxComments = $_POST[ $data_field_name ];
        $maxComments2 = $_POST[ $data_field_name2 ];
        $maxComments3 = $_POST[ $data_field_name3 ];
        $maxComments4 = $_POST[ $data_field_name4 ];

if($maxComments2=="true")
{
 update_option( $opt_name2, "true" );
}else
{
update_option( $opt_name2, "false" );

}

if($maxComments3=="true")
{
 update_option( $opt_name3, "true" );
}else
{
update_option( $opt_name3, "false" );

}

if($maxComments4=="true")
{
 update_option( $opt_name4, "true" );
}else
{
 update_option( $opt_name4, "false" );
}




        // Save the posted value in the database
        update_option( $opt_name, $maxComments );
        update_option( $opt_name2, $maxComments2 );
       // update_option( $opt_name3, $maxComments3 );
       // update_option( $opt_name4, $maxComments4 );

        // Put an options updated message on the screen

?>
<div class="updated"><p><strong><?php _e('Options saved.', 'mt_trans_domain' ); ?></strong></p></div>
<?php

    }

    // Now display the options editing screen

    echo '<div class="wrap">';

    // header

    echo "<h2>" . __( 'Twiogle Twitter Commenter', 'mt_trans_domain' ) . "</h2>";
echo "- gets your archived posts and pages re-indexed because content will be ever changing<br />
- manages the organic page growth of your autoblogs<br />
- injects targeted (based on your tags) content and keywords into your posts<br />
- encourages real readers to add their own comments, no one wants to be the first one to comment<br />
- removes the zero comment footprint common to most blogs<br />";


echo "<p>The Twiogle Twitter commenter plugin pulls in comments from Twitter to your own blog posts.</p>";
    // options form
	echo "<p>This plugin will look at every tag in your post and try to pull tweets from twitter based off of your tags and post that tweet as a comment on your post.</p>";
    echo "<p>Be sure to tag your posts with good tags, do not tag your posts with an unfamiliar word or it may not pull any comments</p>";
		echo "<p>by: <a href='http://twiogle.com'>Twiogle</a></p>";
    ?>

<form name="form1" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
<hr />
<input type="hidden" name="<?php echo $hidden_field_name; ?>" value="Y">

<p><?php _e("Comments per post:", 'mt_trans_domain' ); ?> 
<input type="text" name="<?php echo $data_field_name; ?>" value="<?php echo $maxComments; ?>" size="10">Default is 10
</p>




<p>
<input type="checkbox" name="<?php echo $data_field_name2; ?>" value="true" <?php if($maxComments2=="true"){echo "checked";}?>>
Make external links in tweets nofollow</p>

<p>
<input type="checkbox" name="<?php echo $data_field_name3; ?>" value="true" <?php if($maxComments3=="true"){echo "checked";}?>>
Link back to Twitter profile</p>
<p>
<input type="checkbox" name="<?php echo $data_field_name4; ?>" value="true" <?php if($maxComments4=="true"){echo "checked";}?>>
Remove Hashtags and @'s  (makes it look more like a real comment)</p>




<?php



//echo "testing= $maxComments2";

?>


<p class="submit">
<input type="submit" name="Submit" value="<?php _e('Update Options', 'mt_trans_domain' ) ?>" />
</p>

</form>
</div>
<p>
You can view the next time the comments will run with <a href='http://wordpress.org/extend/plugins/cron-view/'>this</a> plugin
</p>
<p>

This plugin will generate 2 comments per day automatically, but if you want to generate 1 comment per post hit the huge button below
</p>
<?php
echo "<form method='POST' action=''>";
echo "<input type='hidden' name='runOnce' value='1' />";
echo "<input type='submit' name='submit' value='Generate 1 Comment Per Post Now' />";

if($_POST['runOnce']=="1")
{
doComments();
}

?>

<p>You should consider donating a few dollars, another plugin just like this was selling for around $100</p>

<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
<input type="hidden" name="cmd" value="_s-xclick">
<input type="hidden" name="hosted_button_id" value="11255361">
<input type="image" src="https://www.paypal.com/en_US/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
<img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1">
</form>


<?php
 
}

function doComments()
{
$maxComments = get_option( 'mt_posts_per_page' );

if($maxComments== null || $maxComments== "")
{
$maxComments=10;
}

global $wpdb;
//$allposts = $wpdb->get_results("SELECT ID, post_title FROM $wpdb->posts, $wpdb->comments WHERE comment_post_ID=ID group by ID having COUNT(comment_post_ID) < $maxComments order by ID Desc ");

$allposts = $wpdb->get_results("SELECT ID, post_title FROM $wpdb->posts where post_type ='post' and post_status='publish' order by ID Desc ");

foreach ($allposts as $apost) {
	//echo $fivesdraft->post_title;
	$id= $apost->ID;	

$defaults34 = array(
    'status' => 'approve',
    'post_id' => "$id");


	//$comments = get_comments("post_id=$id&status=approve");
$comments = get_comments($defaults34);

	
	$count= count($comments);

//echo "<p>found $count comments for this post </p>";

	if($count < $maxComments)
	{
	  postComment($id);
	  
	}
	               
}
}


function postComment($id) {
global $wpdb;
//$id='11585';
$makeNoFollow = get_option( 'twiogle_make_no_follow' );
$linkBack = get_option( 'link_back_to_profile' );
$removeAtsAndHash = get_option( 'remove_hashtags' );

if(!$removeAtsAndHash=="true")
{
$removeAtsAndHash="false";
}

$keyword="";

//print_r($wpdb);

$prefix= $wpdb->prefix;

$tags = $wpdb->get_results("SELECT * FROM  $wpdb->terms, $wpdb->term_relationships , $wpdb->term_taxonomy
WHERE  $wpdb->term_relationships.object_id='$id' and $wpdb->terms.term_id=$wpdb->term_taxonomy.term_id  and $wpdb->term_relationships.term_taxonomy_id=$wpdb->term_taxonomy.term_taxonomy_id and taxonomy='post_tag'");



	foreach ($tags as $kid) {


	$name= $kid->name;
	$keyword= $keyword . " " . $name;

}

$keyword= str_replace("win", "", $keyword);
$keyword= trim($keyword);
echo "<br /><p>attempting to post id=$id  with Keywords= $keyword </p>";


if($keyword=="" || $keyword==" ")
{
echo "<br />No tags on post ID= $id, <b>put some tags on this post!</b>";
return;
}
	echo "<br />";


//echo $keyword;


$twitter_query = $keyword;
$search = new TwitterSearch($twitter_query);
$results = $search->results();

//echo count($results);

if(count($results) >0)
{
foreach($results as $result){

//echo "<pre>";
//print_r($result);
//echo "</pre>";
 
$username=$result->from_user;
$imgURL=$result->profile_image_url;
$twitterid= $result->id;

//echo "<br />twitterid=$twitterid <br />";
$text_n = toLink($result->text);

$twitAry=split(" ",$text_n);

$commentText="";

foreach($twitAry as $txt)
{

$position= strpos($txt,'@');

if(strpos($txt,'@')===FALSE && strpos($txt,'#')===FALSE)
{


$commentText= $commentText ." $txt";


}else if(strpos($txt,'@')==0 && strpos($txt,'#')===FALSE)
{

if($removeAtsAndHash=="false")
{
$noat= str_replace("@", "", $txt);
$commentText=$commentText. " <a rel='nofollow' target='_blank' href='http://twitter.com/$noat'>$txt</a>";
}

}else if(strpos($txt,'#')==0)
{
if($removeAtsAndHash=="false")
{
$nohash= str_replace("#", "", $txt);
$commentText=$commentText. " <a rel='nofollow' target='_blank' href='http://search.twitter.com/search?q=$nohash'>$txt</a>";
}
}


 
}

$commentText=str_replace("rt", "" ,$commentText);
$commentText=str_replace("RT", "" ,$commentText);
$commentText=str_replace("Rt", "" ,$commentText);

$prefix=$wpdb->prefix;
$twitterTableName= "$prefix" ."twiogle_comments";
$commentsTableName= "$prefix" ."comments";
//echo $twitterTableName;

//check if the comment already exists
//if comment already exists do a contiune;
$commentExists = $wpdb->get_results("SELECT * FROM $twitterTableName where twitterid='$twitterid'");

$doContinue="false";

foreach ($commentExists as $comm) {

if($comm->twitterid== $twitterid)
{
//this comment exists, lets look for another!
$doContinue="true";
continue;
}
}


if($doContinue=="true")
{
$doContinue="false";
continue;
}


//now insert the comment

if($makeNoFollow=="true")
{
    $commentText= str_replace("a href=","a rel='nofollow' href=", $commentText);
}

$commentText=$wpdb->escape($commentText);



$linkBackURL="";
if($linkBack=="true")
{
$linkBackURL="http://twitter.com/". $username;
}else
{
$linkBackURL="";
}

$commentInsert = $wpdb->query("insert into $commentsTableName (comment_post_ID, comment_author, comment_content, comment_approved,comment_date, comment_date_gmt, comment_author_url) VALUES ('$id','$username', '$commentText', '1', now(), UTC_TIMESTAMP(), '$linkBackURL' )  ");

//echo "insert into $commentsTableName (comment_post_ID, comment_author, comment_content, comment_approved,comment_date, comment_date_gmt, comment_author_url) VALUES ('$id','$username', '$commentText', '1', now(), UTC_TIMESTAMP(), '$linkBackURL' )  ";

echo "<p><font color='blue'>$commentText </font></p>";

//now tell the DB that this twitter comment has been posted.
$nothing = $wpdb->query("insert into $twitterTableName VALUES ('$twitterid') ");

//echo "just posted a comment on postID=$id";

//once the comment is posted and good we break
break;
}


}


echo "<p>unable to find a new tweet for this post</p>";
}

$twiogle_commenter_db_version = "1.1";

function twiogle_commenter_install() {
   global $wpdb;
   global $twiogle_commenter_db_version;

   $table_name = $wpdb->prefix . "twiogle_comments";
   if($wpdb->get_var("show tables like '$table_name'") != $table_name) {
      
      $sql = "CREATE TABLE " . $table_name . " (
	  twitterid bigint(100) NOT NULL,
         PRIMARY KEY  (twitterid),
         KEY twitterid (twitterid)
);";

      require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
      dbDelta($sql);



 
      add_option("twiogle_commenter_db_version", $twiogle_commenter_db_version);

   }
}


?>
