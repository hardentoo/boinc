<?php
// $Id: user-profile.tpl.php,v 1.2.2.2 2009/10/06 11:50:06 goba Exp $

/**
 * @file user-profile.tpl.php
 * Default theme implementation to present all user profile data.
 *
 * This template is used when viewing a registered member's profile page,
 * e.g., example.com/user/123. 123 being the users ID.
 *
 * By default, all user profile data is printed out with the $user_profile
 * variable. If there is a need to break it up you can use $profile instead.
 * It is keyed to the name of each category or other data attached to the
 * account. If it is a category it will contain all the profile items. By
 * default $profile['summary'] is provided which contains data on the user's
 * history. Other data can be included by modules. $profile['user_picture'] is
 * available by default showing the account picture.
 *
 * Also keep in mind that profile items and their categories can be defined by
 * site administrators. They are also available within $profile. For example,
 * if a site is configured with a category of "contact" with
 * fields for of addresses, phone numbers and other related info, then doing a
 * straight print of $profile['contact'] will output everything in the
 * category. This is useful for altering source order and adding custom
 * markup for the group.
 *
 * To check for all available data within $profile, use the code below.
 * @code
 *   print '<pre>'. check_plain(print_r($profile, 1)) .'</pre>';
 * @endcode
 *
 * Available variables:
 *   - $user_profile: All user profile data. Ready for print.
 *   - $profile: Keyed array of profile categories and their items or other data
 *     provided by modules.
 *
 * @see user-profile-category.tpl.php
 *   Where the html is handled for the group.
 * @see user-profile-item.tpl.php
 *   Where the html is handled for each item in the group.
 * @see template_preprocess_user_profile()
 */
?>
<?php

global $user;
drupal_set_title('');
$account = user_load($account->uid);
$content_profile = content_profile_load('profile', $account->uid);
$name = check_plain($account->boincuser_name);
$join_date = date('d F Y', $account->created);
$country = check_plain($content_profile->field_country[0]['value']);
$website = check_plain($content_profile->field_url[0]['value']);
$background = check_markup($content_profile->field_background[0]['value'], $content_profile->format, FALSE);
$opinions = check_markup($content_profile->field_opinions[0]['value'], $content_profile->format, FALSE);
$user_links = array();
$profile_is_approved = ($content_profile->status AND !$content_profile->moderate);
$user_is_moderator = user_access('edit any profile content');
$is_own_profile = ($user->uid == $account->uid);

if ($user->uid AND ($user->uid != $account->uid)) {
  if (module_exists('private_messages')) {
  // if (function_exists('privatemsg_get_link')) {
    $user_links[] = array(
      'title' => bts('Send message'),
      'href' => privatemsg_get_link(array($account))
    );
  }
  
  if (module_exists('friends')) {
    $flag = flag_get_flag('friend');
    $friend_status = flag_friend_determine_friend_status($flag, $account->uid, $user->uid);
    switch ($friend_status) {
    case FLAG_FRIEND_BOTH:
    case FLAG_FRIEND_FLAGGED:
      $user_links[] = array(
        'title' => bts('Remove friend'),
        'href' => "flag/confirm/unfriend/friend/{$account->uid}"
      );
      break;
    case FLAG_FRIEND_PENDING:
      $user_links[] = array(
        'title' => bts('Cancel friend request'),
        'href' => "flag/confirm/unflag/friend/{$account->uid}"
      );
      break;
    case FLAG_FRIEND_APPROVAL:
       $user_links[] = array(
        'title' => bts('Approve friend request'),
        'href' => "flag/confirm/flag/friend/{$account->uid}"
      );
      break;
    case FLAG_FRIEND_UNFLAGGED:
    default:
      $user_links[] = array(
        'title' => bts('Add as friend'),
        'href' => "flag/confirm/flag/friend/{$account->uid}"
      );
    }
  }
  
  if (user_access('assign community member role')
      OR user_access('assign all roles')) {
    if (array_search('community member', $account->roles)) {
      $user_links[] = array(
        'title' => bts('Ban user'),
        'href' => "moderate/user/{$account->uid}/ban"
      );
    }
    else {
      $user_links[] = array(
        'title' => bts('Lift user ban'),
        'href' => "user_control/{$account->uid}/lift-ban"
      );
    }
  }
}

?>
<div class="user-profile">
  <div class="picture">
    <?php 
      $user_image = boincuser_get_user_profile_image($account->uid, FALSE);
      print theme('imagefield_image', $user_image['image'], $user_image['alt'],
        $user_image['alt'], array(), false);
    ?>
  </div>
  <div class="general-info">
    <div class="name">
      <span class="label"></span>
      <span class="value"><?php print $name; ?></span>
    </div>
    <div class="join-date">
      <span class="label"><?php print bts('Member since'); ?>:</span>
      <span class="value"><?php print $join_date; ?></span>
    </div>
    <div class="country">
      <span class="label"><?php print bts('Country'); ?>:</span>
      <span class="value"><?php print $country; ?></span>
    </div>
    <?php if ($website AND ($profile_is_approved OR $user_is_moderator OR $is_own_profile)): ?>
      <div class="website">
        <span class="label"><?php print bts('Website'); ?>:</span>
        <span class="value"><?php print l($website, (strpos($website, 'http') === false) ? "http://{$website}" : $website); ?></span>
      </div>
    <?php endif; ?>
    <?php if ($user->uid AND ($user->uid != $account->uid)): ?>
      <ul class="tab-list">
        <?php foreach ($user_links as $key => $link): ?>
          <li class="primary <?php print ($key == 0) ? 'first ' : ''; ?>tab<?php print ($key == count($user_links)-1) ? ' last' : ''; ?>">
            <?php print l($link['title'], $link['href'], array('query' => drupal_get_destination())); ?>
          </li>
          <!--
          <?php if (module_exists('private_messages')): ?>
            <li class="first tab"><?php print l(bts('Send message'), privatemsg_get_link(array($account)), array('query' => drupal_get_destination())); ?></li>
          <?php endif; ?>
          <li class="last tab"><?php print l(bts('Add as friend'), "flag/confirm/flag/friend/{$account->uid}", array('query' => drupal_get_destination())); ?></li>
          -->
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
    <div class="clearfix"></div>
  </div>
  <?php if ($background OR $opinions): ?>
    <div class="bio">
      <?php if (!$profile_is_approved): ?>
        <div class="messages warning">
          <?php print bts('Profile awaiting moderator approval'); ?>
        </div>
      <?php endif; ?>
      <?php if ($profile_is_approved OR $user_is_moderator OR $is_own_profile): ?>
        <?php if ($background): ?>
          <div class="background">
            <span class="label"><?php print bts('Background'); ?></span>
            <span class="value"><?php print $background; ?></span>
          </div>
        <?php endif; ?>
        <?php if ($opinions): ?>
          <div class="opinions">
            <span class="label"><?php print bts('Opinion'); ?></span>
            <span class="value"><?php print $opinions; ?></span>
          </div>
        <?php endif; ?>
      <?php endif; ?>
    </div>
  <?php endif; ?>
</div>
