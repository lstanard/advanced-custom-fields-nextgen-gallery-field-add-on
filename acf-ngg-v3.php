<?php


if( !class_exists( 'ACF_NGGallery_Field' ) && class_exists( 'acf_Field' ) ) :

/**
 * Advanced Custom Fields - NGGallery Field add-on
 * 
 * @author Ales loziak <ales.loziak@gmail.com>
 * @version 1.2
 */
class ACF_NGGallery_Field extends acf_Field 
{
   
   /**
    * WordPress Localization Text Domain
    * 
    * The textdomain for the field is controlled by the helper class.
    * @var string
    */
   private $l10n_domain;

   private $helper;
   
   /**
    * Class Constructor - Instantiates a new NGGallery Field
    */
   public function __construct($parent) {
      

      parent::__construct($parent);

      error_log("plugin constuctor");

      //Get the textdomain from the Helper class
      $this->l10n_domain = acf_nggallery_field_plugin::L10N_DOMAIN;

      $this->name  = 'nggallery-field';
      $this->category = __("Relational", 'acf'); // Basic, Content, Choice, etc

      $post_title = ( !class_exists('nggdb') ) ? '. ' . __( 'NextGEN Gallery plugin is not installed or activated!', $this->l10n_domain ) : false;

      $this->title = __( 'NextGEN Gallery'.$post_title, $this->l10n_domain );
      $this->label = __( 'NextGEN Gallery'.$post_title, $this->l10n_domain );
      
      $this->defaults = array(
           'input_type'       => 'select'
         , 'allow_null'       => true
         , 'input_size'       => 5
         , 'nextgen_type'   => 'Galleries and Albums'
      );

   }
   
   /**
    * Creates the nggallery field for inside post metaboxes
    * 
    * @see acf_Field::create_field()
    */
   public function create_field( $field ) {
      global $ngg, $nggdb, $wp_query;
      
      $field = array_merge($this->defaults, $field);
      
      $values = $field[ 'value' ];
      
      if ( !empty($values[0]) ):
         
         foreach ( $values as $form ) {
               
            if ( in_array ( 'gallery', $form ) )
               $values_gallery[]=$form['ngg_id'];
         
            if ( in_array ( 'album', $form ) )
               $values_album[]=$form['ngg_id'];
               
         }
         
      endif;
      
      if ( class_exists('nggdb') ) :
      
         // Settings of NextGEN Gallery SQL query
         $limit = 0;
         $start = 0;
         $order_by = 'title';
         $order_dir = 'ASC';
         
         // Seek to all NextGEN Galleries
         $gallerylist = $nggdb->find_all_galleries( $order_by, $order_dir , TRUE, $limit, $start, false);
         $albumlist = $nggdb->find_all_album( 'name', $order_dir, $limit, $start);
         
         $haystack = array( 'select', 'multiselect' );
         if( in_array( $field[ 'input_type' ],  $haystack ) ) :
         ?>
            <select name="<?php echo $field[ 'name' ]; ?>[]" id="<?php echo $field[ 'name' ]; ?>" class="<?php echo $field[ 'class' ]; ?>" <?php echo ( $field[ 'input_type' ] == 'multiselect' ) ? 'multiple="multiple" size="' . $field[ 'input_size' ] . '"' : ''; ?>>
               <?php if($field['allow_null'] == '1') echo '
                  <option value="null"> - Select - </option>';
               ?>
               
               <optgroup label="<?php _e('Galleries','nggallery'); ?>">
               <?php foreach( $gallerylist as $gallery ) : ?>
                  <option value="<?php echo $gallery->gid.',gallery'; ?>"<?php if ( $values_gallery ) selected( in_array( $gallery->gid, $values_gallery ) ); ?>><?php echo $gallery->title; ?></option>
               <?php endforeach; ?>
               </optgroup>
               <optgroup label="<?php _e('Albums','nggallery'); ?>">
               <?php foreach( $albumlist as $album ) : ?>
                  <option value="<?php echo $album->id.',album'; ?>"<?php if ( $values_album ) selected( in_array( $album->id, $values_album ) ); ?>><?php echo $album->name; ?></option>
               <?php endforeach; ?>
               </optgroup>
            </select>
         <?php
         endif;
         
      else:
      ?>
            <select name="<?php echo $field[ 'name' ]; ?>[]" id="<?php echo $field[ 'name' ]; ?>" class="<?php echo $field[ 'class' ]; ?>">
               <option value="0" disabled="true"><?php _e( 'NextGEN Gallery plugin is not installed or activated!', $this->l10n_domain ); ?></option>
            </select>
      <?php
      endif;
            
   }
   
   /**
    * Builds the field options
    * 
    * @see acf_Field::create_options()
    * @param string $key
    * @param array $field
    */
   public function create_options( $key, $field ) {
      $field = array_merge($this->defaults, $field);
      
      ?>
         <tr class="field_option field_option_<?php echo $this->name; ?>">
         <td class="label">
            <label><?php _e("Allow Null?",'acf'); ?></label>
         </td>
         <td>
            <?php 

            // do it the old way    
            $this->parent->create_field(array(
               'type'   => 'radio',
               'name'   => 'fields['.$key.'][allow_null]',
               'value'  => $field['allow_null'],
               'choices'   => array(
                  '1'   => 'Yes',
                  '0'   => 'No',
               ),
               'layout' => 'horizontal',
            ));
            ?>
         </td>
      </tr>

      <tr class="field_option field_option_<?php echo $this->name; ?>">
         <td class="label">
            <label><?php _e( 'Nextgen Type' , $this->domain ); ?></label>
            <p class="description"><?php _e( 'Allow or restrict the Nextgen selection type', $this->domain ); ?></p>
         </td>
         <td>
                <?php

                // do it the old way
                  $this->parent->create_field( array(
                     'type'    => 'select',
                     'name'    => "fields[{$key}][nextgen_type]",
                     'value'   => $field[ 'nextgen_type' ],
                     'class'   => "nggallery_nextgen_type nggallery_nextgen_type_{$key}",
                     'choices' => array(
                        'Galleries and Albums'    => __( 'Galleries and Albums', $this->domain )
                        , 'Galleries' => __( 'Galleries', $this->domain )
                        , 'Albums' => __( 'Albums', $this->domain )
                      )
                  ) );
            ?>
         </td>
      </tr>

         <tr class="field_option field_option_<?php echo $this->name; ?>">
            <td class="label">
               <label><?php _e( 'Input Method' , $this->l10n_domain ); ?></label>
               <p class="description"><?php _e( '', $this->l10n_domain ); ?></p>
            </td>
            <td>
               <?php

                  // do it the old way
                  $this->parent->create_field( array(
                     'type'    => 'select',
                     'name'    => "fields[{$key}][input_type]",
                     'value'   => $field[ 'input_type' ],
                     'class'   => "nggallery_input_type nggallery_input_type_{$key}",
                     'choices' => array(
                        'select'      => 'Select',
                        'multiselect' => 'Multi-Select',
                        //'token'       => 'Input Tokenizer',
                        )
                  ) );

               ?>
            </td>
         </tr>
         <tr id="nggallery_input_size[<?php echo $key; ?>]" class="field_option field_option_<?php echo $this->name; ?> nggallery_input_size nggallery_input_size_<?php echo $key; ?>">
            <td class="label">
               <label><?php _e( 'Multi-Select Size' , $this->l10n_domain ); ?></label>
               <p class="description"><?php _e( 'The number of rows to show at once in a multi-select.', $this->l10n_domain ); ?></p>
            </td>
            <td>
               <?php 
                  // do it the old way
                  $this->parent->create_field( array(
                     'type'    => 'select',
                     'name'    => "fields[{$key}][input_size]",
                     'value'   => $field[ 'input_size' ],
                     'choices' => array_combine( range( 3, 15, 2 ), range( 3, 15, 2 ) ),
                  ) );
               ?>
            </td>
         </tr>
         <script type='text/javascript'> 
    
            jQuery(document).ready(function() {
               
               if ( jQuery('.nggallery_input_type_<?php echo $key; ?>').val()=='select' ) jQuery('.nggallery_input_size_<?php echo $key; ?>').hide();
               else jQuery('.nggallery_input_size_<?php echo $key; ?>').show();
               
               jQuery('.nggallery_input_type_<?php echo $key; ?>').change(function() {
                  if ( jQuery('.nggallery_input_type_<?php echo $key; ?>').val()=='select' ) jQuery('.nggallery_input_size_<?php echo $key; ?>').hide();
                  else jQuery('.nggallery_input_size_<?php echo $key; ?>').show();
               });
            });
            
         </script>
      <?php
   }
   
   /**
    * (non-PHPdoc)
    * @see acf_Field::update_value()
    */
   public function update_value( $post_id, $field, $value ) {
         
      $field = array_merge($this->defaults, $field);
      
      foreach( $value as $key=>$item ) {
         $items = explode( ',', $item );
         foreach( $items as $item ) {
            if( is_numeric( $item ) )
               $values[$key]['ngg_id'] = intval ( $item );
            else
               $values[$key]['ngg_form'] = strval( $item );
            }
      }
      
      parent::update_value( $post_id, $field, $values );
   }
   
   /**
    * Returns the values of the field
    * 
    * @see acf_Field::get_value()
    * @param int $post_id
    * @param array $field
    * @return mixed  
    */
   public function get_value( $post_id, $field ) {
      $value = (array) parent::get_value( $post_id, $field );
      return $value;
   }
   
   /**
    * Returns the value of the field for the advanced custom fields API
    * 
    * @see acf_Field::get_value_for_api()
    * @param int $post_id
    * @param array $field
    * @return string
    */
   public function get_value_for_api( $post_id, $field ) {
      return parent::get_value_for_api($post_id, $field);
   }
}

endif;

?>