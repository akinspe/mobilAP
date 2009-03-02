function confirmClick(e)
{
	var msg = 'Are you sure?';
    if (!confirm(msg)) {
        Event.stop(e);
        return false;
    }
    
    return true;
}

function mobilAP_init()
{
	$$('.confirm').invoke('observe', 'click', confirmClick);
}

window.onload = mobilAP_init;