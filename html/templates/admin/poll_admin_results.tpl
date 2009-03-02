<h1>Results for {$poll_name}</h1>

{foreach from=$votes item=ballot}
{assign var=vote_count value=0}
<h2>{$ballot->ballot_name}</h2>

<ul>
{foreach from=$ballot->votes key=vote_userID item=count}
<li>{$vote_userID|getFullName}: {$count}</li>
{assign var=vote_count value=$vote_count+$count}
{/foreach}
</ul>

<p>Total Votes: {$vote_count}</p>
{/foreach}

