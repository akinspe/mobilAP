<?php include('nav.tpl');

$attendee_summary = mobilAP_attendee::getAttendeeSummary();

?>
<div class="content">
<h1>Welcome</h1>

<p>There are <?= $attendee_summary['total'] ?> attendees representing <?= $attendee_summary['organizations_count'] ?> organizations from <?= $attendee_summary['states_count'] ?> states attending this event</p>

<img src="<?= mobilAP_attendee::getWelcomeImageSrc() ?>">
</div>