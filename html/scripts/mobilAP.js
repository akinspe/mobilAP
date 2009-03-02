function confirmClick(e, msg)
{
	if (!msg) msg = 'Are you sure?';
    if (!confirm(msg)) {
        e.stopPropagation();
        return false;
    }
    
    return true;
}

function mobilAP_init()
{
	$('.confirm').click(confirmClick);
}

$(mobilAP_init);