<h2>Session Evaluation</h2>

<p>We appreciate your feedback. Please answer the following brief questions and include additional comments if you wish.</p>
<form action="session.php" id="evaluation_form" method="POST">
<input type="hidden" name="session_id" value="<?= $session->session_id ?>">
<input type="hidden" name="view" value="<?= $view ?>">

<h3>1. Value of Session</h3>
<ul class="evaluation_responses">
	<li><input type="radio" name="responses[0]" value="1"> Exceptional</li>
	<li><input type="radio" name="responses[0]" value="2"> Good</li>
	<li><input type="radio" name="responses[0]" value="3"> Fair</li>
	<li><input type="radio" name="responses[0]" value="4"> Poor</li>
</ul>
	
<h3>2. Relevance of Information Presented</h3>
<ul class="evaluation_responses">
	<li><input type="radio" name="responses[1]" value="1"> Exceptional</li>
	<li><input type="radio" name="responses[1]" value="2"> Good</li>
	<li><input type="radio" name="responses[1]" value="3"> Fair</li>
	<li><input type="radio" name="responses[1]" value="4"> Poor</li>
</ul>
	
<h3>3. Effectiveness of Presenters</h3>
<ul class="evaluation_responses">
	<li><input type="radio" name="responses[2]" value="1"> Exceptional</li>
	<li><input type="radio" name="responses[2]" value="2"> Good</li>
	<li><input type="radio" name="responses[2]" value="3"> Fair</li>
	<li><input type="radio" name="responses[2]" value="4"> Poor</li>
</ul>
	
<h3>4. From what I learned in this session I will make changes in my work/department/institution</h3>
<ul class="evaluation_responses">
	<li><input type="radio" name="responses[3]" value="1"> Definately Yes</li>
	<li><input type="radio" name="responses[3]" value="2"> Maybe</li>
	<li><input type="radio" name="responses[3]" value="3"> Possibly</li>
	<li><input type="radio" name="responses[3]" value="4"> Unlikely</li>
</ul>

<h3>5.Other Comments</h3>
<textarea name="responses[4]" cols="50" rows="8"></textarea>
<br>
<input type="submit" name="submit_evaluation" value="Submit Post">
</form>
	