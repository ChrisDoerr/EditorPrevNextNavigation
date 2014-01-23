<?php
/**
 * Editor PrevNext Navigation Core Class.
 *
 * @category    Editor PrevNext Navigation
 * @copyright   Copyright (c) 2014 http://www.meomundo.com
 * @author      Christian Doerr <doerr@meomundo.com>
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2 (GPL-2.0)
 */
class EditorPrevNextNavigation {

  /**
   * @var object $DB WordPress database object.
   */
  protected $DB;
  
  /**
   * The constructor.
   *
   * @param object $wpdb WordPress database object.
   */
  public function __construct( $wpdb ) {
    
    $this->DB     = $wpdb;

    /**
     * Load the Prev/Next navigation meta box (only) when opening an existin post in the editor.
     */
    if( is_admin() ) {
  
      add_action( 'load-post.php', array( $this, 'metaBoxSetup' ) );

    }

  }

  /**
   * Register the new custom meta box.
   * Since it will only contain prev/next links and no actual input
   * no "saving" etc is necessary.
   */
  public function metaBoxSetup() {

    add_action( 'add_meta_boxes', array( $this, 'addMetaBox' ) );

  }

  /**
   * Actually create the new custom meta box.
   */
  public function addMetaBox() {
  
    /** 
     * You only want the meta box to appear when editing post, pages or custom post types.
     * So the easiest way to achieve this is to exclude certain post types and basically
     * allow all the others.
     */
    $blacklist  = array( 'attachement', 'revision', 'nav_menu_item' );

    $postTypes  = get_post_types();
  
    foreach( $postTypes as $postType ) {
   
      if( !in_array( $postType, $blacklist ) ) {
    
        add_meta_box(
          'editorPrevNextNavigation',
          _x( 'Editor Navigation', 'eppn' ),
          array( $this, 'viewMetaBox' ),
          $postType,
          'side',
          'default'
        );
    
      }
  
    }

  }

  /**
   * Controller: Build and view the prev/next navigation.
   *
   * @param object Post object (automatically being provided by the internal WordPress API call).
   */
  public function viewMetaBox( $object ) {
  
    /**
     * Only browse through the same post type as the current one.
     */
    $postType   = get_post_type( $object->ID );
    
    if( $postType === false ) {

      $postType = 'post';
 
    }
    
    $postID     = $this->getCurrentPostID( $object->ID );
    
    $nextPostID = $this->getNextPostID( $postID, $postType );

    $prevPostID = $this->getPreviousPostID( $postID, $postType );
    
    $html       = $this->generateHTML( $nextPostID, $prevPostID );
    
    echo $html;
    
  }
  
  /**
   * Get the "real" current post ID, not a revision ID!
   *
   * @param   int $postID   Post (object) ID.
   * @return  int           Post ID.
   */
  public function getCurrentPostID( $postID ) {
    
    $postID   = (int) $postID;

    $parentID = (int) $this->DB->get_var( "SELECT {$this->DB->posts}.post_parent FROM {$this->DB->posts} WHERE {$this->DB->posts}.id = {$postID}" );

    return ( $parentID === 0 ) ? $postID : $parentID;

  }
  
  /**
   * Get the ID of the next post (object).
   *
   * @param   int     $postID     Post (object) ID.
   * @param   string  $postType   Post type to browse through.
   * @return  int                 ID of the next post (object).
   */
  public function getNextPostID( $postID, $postType ) {
    
    $postID = (int) $postID;
    
    $query      = $this->DB->prepare(  "SELECT {$this->DB->posts}.ID FROM {$this->DB->posts} WHERE {$this->DB->posts}.ID > {$postID} AND {$this->DB->posts}.post_type = %s AND {$this->DB->posts}.post_status = 'publish' ORDER BY {$this->DB->posts}.ID ASC LIMIT 1", $postType );

    $nextPostID = $this->DB->get_var( $query );

    return ( $nextPostID !== null ) ? $nextPostID : 0;

  }
  
  /**
   * Get the ID of the previous post (object).
   *
   * @param   int     $postID     Post (object) ID.
   * @param   string  $postType   Post type to browse through.
   * @return  int                 ID of the previous post (object).
   */
  public function getPreviousPostID( $postID, $postType ) {
    
    $postID = (int) $postID;
    
    $query  = $this->DB->prepare( "SELECT {$this->DB->posts}.ID FROM {$this->DB->posts} WHERE {$this->DB->posts}.ID < {$postID} AND {$this->DB->posts}.post_type = %s AND {$this->DB->posts}.post_status = 'publish' ORDER BY {$this->DB->posts}.ID DESC LIMIT 1", $postType );

    $prevPostID = $this->DB->get_var( $query );
  
    return ( $prevPostID !== null ) ? (int) $prevPostID : 0;

  }
  
  /**
   * Generate the actaul prev/next navigation links
   * for the meta box.
   *
   * @param   int     $nextPostID ID of the next post (object).
   * @param   int     $prevPostID ID of the previous post (object).
   * @return  string              Prev/next navigation HTML.
   */
  public function generateHTML( $nextPostID, $prevPostID ) {

    $nextPostID = (int) $nextPostID;

    $prevPostID = (int) $prevPostID;

    $prevHTML   = '';
    $nextHTML   = '';
    $html       = '';

    if( $nextPostID > 0 ) {

      $nextHTML .= ' <a href="post.php?post=' . $nextPostID . '&action=edit" class="button-primary">&laquo; ' . _x( 'Next Post', 'epnn' ) . '</a>';
      
    }

    if( $prevPostID > 0 ) {
    
      $prevHTML .= ' <a href="post.php?post=' . $prevPostID . '&action=edit"class="button-primary">' . _x( 'Previous Post', 'epnn' ) . ' &raquo;</a>';
      
    }
    
    $html .= '<script>';

    if( $prevPostID > 0 || $nextPostID > 0 ) {
    
      $html .= "jQuery('<div style=\"margin-left:1em;maring-right:1em;display:inline-block;\">" . $nextHTML . $prevHTML. "</div>').appendTo(jQuery('#wpbody-content .wrap h2'));";
    
    }

    $html .= "jQuery('#editorPrevNextNavigation').hide();</script>";
    
    return $html;
    
  }

}
?>