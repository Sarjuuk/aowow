var vi_managedRow     = null;
var vi_getAll         = false;
var vim_ViewedRow     = null;
var vim_videoData     = [];
var vim_videoPages    = [];
var vim_numPagesFound = 0;
var vim_numPages 	  = 0;
var vim_numPending 	  = 0;
var vim_statuses 	  = {
    0  : 'Pending',
    999: 'Deleted',
    100: 'Approved',
    105: 'Sticky'
};

function makePipe() {
	var sp = $WH.ce('span');
	$WH.ae(sp, $WH.ct(' '));

	var b = $WH.ce('small');
	b.className = 'q0';
	$WH.ae(b, $WH.ct('|'));

	$WH.ae(sp, b);
	$WH.ae(sp, $WH.ct(' '));

	return sp;
}

function vi_OnResize() {
	var a = Math.max(100, Math.min($WH.g_getWindowSize().h - 50, 700));

	$WH.ge('menu-container').style.height = $WH.ge('pages-container').style.height = a + 'px';
	$WH.ge('data-container').style.height = a + 'px';
}

$WH.aE(window, 'resize', vi_OnResize);

function vi_Refresh(openNext, type, typeId) {
	new Ajax('?admin=videos&action=list' + (vi_getAll ? '&all': ''), {
		method: 'get',
		onSuccess: function (xhr) {
			eval(xhr.responseText);

			if (vim_videoPages.length > 0) {
				$WH.ge('show-all-pages').innerHTML = ' &ndash; <a href="?admin=videos&amp;all">Show All</a> (' + vim_numPagesFound + ')';

				vim_UpdatePages();

				if (openNext)
					vi_Manage($WH.ge('pages-container').firstChild.firstChild, vim_videoPages[0].type, vim_videoPages[0].typeId, true);
				else if (type && typeId)
					vi_Manage(null, type, typeId, true);
			}
			else {
				$WH.ee($WH.ge('show-all-pages'));
				$WH.ge('pages-container').innerHTML = 'NO VIDEOZ NEEDS 2 BE APPRVED NOW KTHX. :)';
				if (type && typeId)
					vi_Manage(null, type, typeId, true);
			}
		}
	})
}

function vi_Manage(_this, type, typeId, openNext) {
	new Ajax('?admin=videos&action=manage&type=' + type + '&typeid=' + typeId, {
		method: 'get',
		onSuccess: function (xhr) {

			eval(xhr.responseText);
			vim_numPending = 0;

			for (var i in vim_videoData)
				if (vim_videoData[i].pending)
					vim_numPending++;

			var nRows = vim_videoData.length;
			$WH.ge('videoTotal').innerHTML = nRows + ' total' + (nRows == 100 ? ' (limit reached)' : '');

			vim_UpdateList(openNext);
			vim_UpdateMassLinks();

			if (vi_managedRow != null)
				vi_ColorizeRow('transparent');

			vi_managedRow = _this;

			if (vi_managedRow != null)
				vi_ColorizeRow('#282828');
		}
	});
}

function vi_ManageUser() {
	var username = $WH.ge('usermanage');
	username.value = $WH.trim(username.value);

	if (username.value.length < 4) {
		alert('Username must be at least 4 characters long.');
		username.focus();

		return false
	}

	if (username.value.match(/[^a-z0-9]/i) != null) {
		alert('Username can only contain letters and numbers.');
		username.focus();

		return false
	}

	new Ajax('?admin=videos&action=manage&user=' + username.value, {
		method: 'get',
		onSuccess: function (xhr) {
			eval(xhr.responseText);

			var nRows = vim_videoData.length;
			$WH.ge('videoTotal').innerHTML = nRows + ' total' + (nRows == 100 ? ' (limit reached)' : '');

			vim_UpdateList();
			vim_UpdateMassLinks();

			if (vi_managedRow != null)
				vi_ColorizeRow('transparent');
		}
	});

	return true
}

function vi_ColorizeRow(color) {
	for (var i = 0; i < vi_managedRow.childNodes.length; ++i)
		vi_managedRow.childNodes[i].style.backgroundColor = color;
}

function vim_GetVideo(id) {
	for (var i in vim_videoData)
		if (vim_videoData[i].id == id)
			return vim_videoData[i];

	return null
}

function vim_View(row, id) {
	if (vim_ViewedRow != null)
		vim_ColorizeRow('transparent');

	vim_ViewedRow = row;
	vim_ColorizeRow('#282828');

	var video = vim_GetVideo(id);
	if (video != null)
		VideoManager.show(video);
}

function vim_ColorizeRow(color) {
	for (var i = 0; i < vim_ViewedRow.childNodes.length; ++i)
		vim_ViewedRow.childNodes[i].style.backgroundColor = color;
}

function vim_ConfirmMassApprove() {
    ajaxAnchor(this);                                       // aowow custom - same endpoint gets used as ajax and page .. what?

    return false;
    // return true;
}

function vim_ConfirmMassDelete() {
    if (confirm('Delete selected video(s)?'))          		// aowow custom - see above
        ajaxAnchor(this);

    return false;
    // return confirm('Delete selected video(s)?');
}

function vim_ConfirmMassSticky() {
    if (confirm('Sticky selected video(s)?'))          		// aowow custom - see above
        ajaxAnchor(this);

    return false;
    // return confirm('Sticky selected video(s)?');
}

function vim_UpdatePages(UNUSED) {
	var pc = $WH.ge('pages-container');
	$WH.ee(pc);

	var tbl = $WH.ce('table');
	tbl.className = 'grid';
	tbl.style.width = '400px';

	var tr = $WH.ce('tr');

	var th = $WH.ce('th');
	$WH.ae(th, $WH.ct('Page'));
	$WH.ae(tr, th);

	th = $WH.ce('th');
	$WH.ae(th, $WH.ct('Submitted'));
	$WH.ae(tr, th);

	th = $WH.ce('th');
	th.align = 'right';
	$WH.ae(th, $WH.ct('#'));
	$WH.ae(tr, th);

	$WH.ae(tbl, tr);

	var now = new Date();
	for (var i in vim_videoPages) {
		var viPage = vim_videoPages[i];
		tr = $WH.ce('tr');
		tr.onclick = vi_Manage.bind(tr, tr, viPage.type, viPage.typeId, true, i);

		var td = $WH.ce('td');
		var a = $WH.ce('a');
		a.href = '?' + g_types[viPage.type] + '=' + viPage.typeId;
		a.target = '_blank';
		$WH.ae(a, $WH.ct(viPage.name));
		$WH.ae(td, a);
		$WH.ae(tr, td);

		td = $WH.ce('td');
		var elapsed = new Date(viPage.date);
		$WH.ae(td, $WH.ct(g_formatTimeElapsed((now.getTime() - elapsed.getTime()) / 1000) + ' ago'));
		$WH.ae(tr, td);

		td = $WH.ce('td');
		td.align = 'right';
		$WH.ae(td, $WH.ct(viPage.count));
		$WH.ae(tr, td);

		$WH.ae(tbl, tr);
	}

	$WH.ae(pc, tbl);
}

function vim_UpdateList(k) {
	var tbl = $WH.ge('theVideosList');
	var tBody = false;
	var i = 1;

	while (tbl.childNodes.length > i) {
		if (tbl.childNodes[i].nodeName == 'TR' && tBody)
			$WH.de(tbl.childNodes[i]);
		else if (tbl.childNodes[i].nodeName == 'TR')
			tBody = true;
		else
			i++;
	}

	var now  = new Date();
	var viId = 0;
	for (var i in vim_videoData) {
		var video = vim_videoData[i];
		var tr = $WH.ce('tr');
		if (viId == 0 && video.pending) {
			viId = video.id;
			tr.id = 'highlightedRow';
		}

		var td = $WH.ce('td');
		td.align = 'center';

		// if (video.status != 999 && !video.pending) {	// Aowow - removed
			var a = $WH.ce('a');
			a.href = $WH.sprintf(vi_siteurls[video.videoType], video.videoId);
			a.target = '_blank';
			a.onclick = function (id, e) {
				$WH.sp(e);
				(vim_View.bind(null, this, id))();
				return false;
			}.bind(tr, video.id);

			var previewImg = $WH.ce('img');
			previewImg.src = $WH.sprintf(vi_thumbnails[video.videoType], video.videoId);
			previewImg.height = 50;
			$WH.ae(a, previewImg);
			$WH.ae(td, a);
		// }
		$WH.ae(tr, td);

		td = $WH.ce('td');
		if (video.status != 999 && !video.pending) {
			var a = $WH.ce('a');
			a.href = '?' + g_types[video.type] + '=' + video.typeId + '#videos:id=' + video.id;
			a.target = '_blank';
			a.onclick = function (a) { $WH.sp(a); };
			$WH.ae(a, $WH.ct(video.id));
			$WH.ae(td, a);
		}
		else
			$WH.ae(td, $WH.ct(video.id));

		$WH.ae(tr, td);

		td = $WH.ce('td');
		td.id = 'title-' + video.id;

		var sp = $WH.ce('span');
		sp.style.paddingRight = '8px';
		if (video.caption) {
			var sp2 = $WH.ce('span');
			sp2.className = 'q2';
			var b = $WH.ce('b');
			$WH.ae(b, $WH.ct(video.caption));
			$WH.ae(sp2, b);
			$WH.ae(sp, sp2);
		}
		else {
			var it = $WH.ce('i');
			it.className = 'q0';
			$WH.ae(it, $WH.ct('NULL'));
			$WH.ae(sp, it);
		}
		$WH.ae(td, sp);

		sp = $WH.ce('span');
		sp.style.whiteSpace = 'nowrap';

		var a = $WH.ce('a');
		a.href = 'javascript:;';
		a.onclick = function (vi, e) {
			$WH.sp(e);
			(vim_ShowEdit.bind(this, vi))();
		}.bind(a, video);
		$WH.ae(a, $WH.ct('Edit'));
		$WH.ae(sp, a);
		$WH.ae(sp, makePipe());

		a = $WH.ce('a');
		a.href = 'javascript:;';
		a.onclick = function (vi, e) {
			$WH.sp(e);
			(vim_Clear.bind(this, vi))();
		}.bind(a, video);
		$WH.ae(a, $WH.ct('Clear'));
		$WH.ae(sp, a);
		$WH.ae(td, sp);
		$WH.ae(tr, td);

		td = $WH.ce('td');
		var elapsed = new Date(video.date);
		$WH.ae(td, $WH.ct(g_formatTimeElapsed((now.getTime() - elapsed.getTime()) / 1000) + ' ago'));
		$WH.ae(tr, td);

		td = $WH.ce('td');
		a = $WH.ce('a');
		a.href = '?user=' + video.user;
		a.target = '_blank';
		a.onclick = function (a) { $WH.sp(a); };
		$WH.ae(a, $WH.ct(video.user));
		$WH.ae(td, a);
		$WH.ae(tr, td);

		td = $WH.ce('td');
		$WH.ae(td, $WH.ct(vim_statuses[video.status]));
		$WH.ae(tr, td);

		td = $WH.ce('td');
		var cb = $WH.ce('input');
		cb.type = 'checkbox';
		cb.value = video.id;
		cb.onclick = function (e) {
			$WH.sp(e);
			(vim_UpdateMassLinks.bind(this))();
		}.bind(cb);
		$WH.ae(td, cb);

		$WH.ae(td, $WH.ct(' '));

		if (video.status != 999) {
			tr.onclick = function (id) {
				vim_View(this, id);
				return false;
			}.bind(tr, video.id);

			if (video.id == viId && k)
				vim_View(tr, video.id);

			if (video.pending) {
				a = $WH.ce('a');
				a.href = 'javascript:;';
				a.onclick = function (e) {
					$WH.sp(e);
					(vim_Approve.bind(this, false))();
				}.bind(video);
				$WH.ae(a, $WH.ct('Approve'));
				$WH.ae(td, a);
			}
			else
				$WH.ae(td, $WH.ct('Approve'));

			$WH.ae(td, makePipe());

			if (video.status != 105) {
				a = $WH.ce('a');
				a.href = 'javascript:;';
				a.onclick = function (e) {
					$WH.sp(e);
					(vim_Sticky.bind(this, false))();
				}.bind(video);
				$WH.ae(a, $WH.ct('Make sticky'));
				$WH.ae(td, a);
			}
			else
				$WH.ae(td, $WH.ct('Make sticky'));

			$WH.ae(td, makePipe());

			a = $WH.ce('a');
			a.href = 'javascript:;';
			a.onclick = function (e) {
				$WH.sp(e);
				(vim_Delete.bind(this, false))();
			}.bind(video);
			$WH.ae(a, $WH.ct('Delete'));
			$WH.ae(td, a);

			$WH.ae(td, makePipe());

			a = $WH.ce('a');
			a.href = 'javascript:;';
			a.onclick = function (e) {
				$WH.sp(e);
				var a = prompt('Enter the ID to move this video to:');
				(vim_Relocate.bind(this, a))();
			}.bind(video);
			$WH.ae(a, $WH.ct('Relocate'));
			$WH.ae(td, a);

			$WH.ae(td, makePipe());

			if (i > 0) {
				a = $WH.ce('a');
				a.href = 'javascript:;';
				a.onclick = function (e) {
					$WH.sp(e);
					(vim_Move.bind(this, -1))()
				}.bind(video);
				$WH.ae(a, $WH.ct('Move up'));
				$WH.ae(td, a);
			}
			else
				$WH.ae(td, $WH.ct('Move up'));

			$WH.ae(td, makePipe());

			if (i < vim_videoData.length - 1) {
				a = $WH.ce('a');
				a.href = 'javascript:;';
				a.onclick = function (e) {
					$WH.sp(e);
					(vim_Move.bind(this, 1))();
				}.bind(video);
				$WH.ae(a, $WH.ct('Move down'));
				$WH.ae(td, a);
			}
			else
				$WH.ae(td, $WH.ct('Move down'));
		}

		$WH.ae(tr, td);
		$WH.ae(tbl, tr);
	}
}

function vim_UpdateMassLinks() {
	var idBuff = '';
	var i = 0;
	var e = $WH.ge('theVideosList');
	var inp = $WH.gE(e, 'input');

	$WH.array_walk(inp, function (i) {
		if (i.checked) {
			idBuff += i.value + ','; ++i
		}
	});

	idBuff = $WH.rtrim(idBuff, ',');

	var selCnt = $WH.ge('withselected');
	if (i > 0) {
		selCnt.style.display = '';
		$WH.gE(selCnt, 'b')[0].firstChild.nodeValue = '(' + i + ')';

		var c = $WH.ge('massapprove');
		var b = $WH.ge('massdelete');
		var a = $WH.ge('masssticky');

		c.href    = '?admin=videos&action=approve&id=' + idBuff;
		c.onclick = vim_ConfirmMassApprove;

		b.href    = '?admin=videos&action=delete&id=' + idBuff;
		b.onclick = vim_ConfirmMassDelete;

		a.href    = '?admin=videos&action=sticky&id=' + idBuff;
		a.onclick = vim_ConfirmMassSticky;
	}
	else
		selCnt.style.display = 'none';
}

function vim_MassSelect(action) {
	var tbl = $WH.ge('theVideosList');
	var inp = $WH.gE(tbl, 'input');

	switch (parseInt(action)) {
		case 1:
			$WH.array_walk(inp, function (x) { x.checked = true; });
			break;
		case 0:
			$WH.array_walk(inp, function (x) { x.checked = false; });
			break;
		case -1:
			$WH.array_walk(inp, function (x) { x.checked = !x.checked; });
			break;
		case 2:
			$WH.array_walk(inp, function (x) { x.checked = vim_GetVideo(x.value).status == 0; });
			break;
		case 5:
			$WH.array_walk(inp, function (x) { x.checked = vim_GetVideo(x.value).unique == 1 && vim_GetVideo(x.value).status == 0; });
			break;
		case 3:
			$WH.array_walk(inp, function (x) { x.checked = vim_GetVideo(x.value).status == 100; });
			break;
		case 4:
			$WH.array_walk(inp, function (x) { x.checked = vim_GetVideo(x.value).status == 105; });
			break;
		default:
			return;
	}

	vim_UpdateMassLinks();
}

function vim_ShowEdit(video, isAlt) {
	var node;
	if (isAlt)
		node = $WH.ge('title2-' + video.id);
	else
		node = $WH.ge('title-' + video.id);

	var sp = $WH.gE(node, 'span')[0];
	var div = $WH.ce('div');
	div.style.whiteSpace = 'nowrap';
	var iCaption = $WH.ce('input');
	iCaption.type = 'text';
	iCaption.value = video.caption;
	iCaption.maxLength = 200;
	iCaption.size = 35;
    iCaption.onclick = function (e) { $WH.sp(e); }          // aowow - custom to inhibit screenshot popup, when clicking into input element
	div.appendChild(iCaption);

	var btn = $WH.ce('input');
	btn.type = 'button';
	btn.value = 'Update';
	btn.onclick = function (vi, isAlt, e) {
		if (!isAlt)
			$WH.sp(e);

		(vim_Edit.bind(this, vi, isAlt))();
	}.bind(btn, video, isAlt);
	div.appendChild(btn);

	var sp2 = $WH.ce('span');
	sp2.appendChild($WH.ct(' '));
	div.appendChild(sp2);

	btn = $WH.ce('input');
	btn.type = 'button';
	btn.value = 'Cancel';
	btn.onclick = function (vi, isAlt, e) {
		if (!isAlt)
			$WH.sp(e);

		(vim_CancelEdit.bind(this, vi, isAlt))();
	}.bind(btn, video, isAlt);
	div.appendChild(btn);

	sp.style.display = 'none';
	sp.nextSibling.style.display = 'none';
	node.insertBefore(div, sp);

	iCaption.focus();
}

function vim_CancelEdit(video, isAlt) {
	var node;
	if (isAlt)
		node = $WH.ge('title2-' + video.id);
	else
		node = $WH.ge('title-' + video.id);

	var b = $WH.gE(node, 'span')[1];
	b.style.display = '';
	b.nextSibling.style.display = '';

	node.removeChild(node.firstChild);
}

function vim_Edit(video, isAlt) {
	var node;
	if (isAlt)
		node = $WH.ge('title2-' + video.id);
	else
		node = $WH.ge('title-' + video.id);

	var desc = node.firstChild.childNodes;
	if (desc[0].value == video.caption) {
		vim_CancelEdit(video, isAlt);
		return;
	}

	video.caption = desc[0].value;

	vim_CancelEdit(video, isAlt);

	node = node.firstChild;
	while (node.childNodes.length > 0)
		node.removeChild(node.firstChild);

	$WH.ae(node, $WH.ct(video.caption));

	new Ajax('?admin=videos&action=edittitle&id=' + video.id, {
		method: 'POST',
		params: 'title=' + $WH.urlencode(video.caption)
	});
}

function vim_Clear(video, isAlt) {
	var node;
	if (isAlt)
		node = $WH.ge('title2-' + video.id);
	else
		node = $WH.ge('title-' + video.id);

	var sp = $WH.gE(node, 'span');
	var a = $WH.gE(sp[1], 'a');
	sp = sp[0];

	if (video.caption == '')
		return;

	video.caption = '';
	sp.innerHTML = "<i class='q0'>NULL</i>";

	new Ajax('?admin=videos&action=edittitle&id=' + video.id, {
		method: 'POST',
		params: 'title=' + $WH.urlencode('')
	});
}

function vim_Approve(openNext) {
	var vi = this;
	new Ajax('?admin=videos&action=approve&id=' + vi.id, {
		method: 'get',
		onSuccess: function (x) {
			Lightbox.hide();
			if (vim_numPending == 1 && vi.pending)
				vi_Refresh(true);
			else {
				vi_Refresh();
				vi_Manage(vi_managedRow, vi.type, vi.typeId, openNext, 0);
			}
		}
	});
}

function vim_Sticky(openNext) {
	var vi = this;
	new Ajax('?admin=videos&action=sticky&id=' + vi.id, {
		method: 'get',
		onSuccess: function (x) {
			Lightbox.hide();
			if (vim_numPending == 1 && vi.pending)
				vi_Refresh(true);
			else {
				vi_Refresh();
				vi_Manage(vi_managedRow, vi.type, vi.typeId, openNext, 0);
			}
		}
	});
}

function vim_Delete(openNext) {
	var vi = this;
	new Ajax('?admin=videos&action=delete&id=' + vi.id, {
		method: 'get',
		onSuccess: function (x) {
			Lightbox.hide();
			if (vim_numPending == 1 && vi.pending)
				vi_Refresh(true);
			else {
				vi_Refresh();
				vi_Manage(vi_managedRow, vi.type, vi.typeId, openNext, 0);
			}
		}
	});
}

function vim_Relocate(typeid) {
	var vi = this;
	new Ajax('?admin=videos&action=relocate&id=' + vi.id + '&typeid=' + typeid, {
		method: 'get',
		onSuccess: function (x) {
			vi_Refresh();
			vi_Manage(vi_managedRow, vi.type, typeid);
		}
	});
}

function vim_Move(direction) {
	var vi = this;
	new Ajax('?admin=videos&action=order&id=' + vi.id + '&move=' + direction, {
		method: 'get',
		onSuccess: function (x) {
			vi_Refresh();
			vi_Manage(vi_managedRow, vi.type, vi.typeId);
		}
	});
}

var VideoManager = new
function () {
	var
		video,
		pos,
		prevImgWidth,
		prevImgHeight,
		scale,
		desiredScale,
		container, screen,
		prevImgDiv,
		aPrev, aNext, aCover,
		aOriginal,
		divFrom,
		spCaption,
		divCaption,
		h2Name,
		controlsCOPY,
		aEdit,
		aClear,
		spApprove,
		aApprove,
		aMakeSticky,
		aDelete,
		loadingImage,
		lightboxComponents;

	function computeDimensions(captionExtraHeight) {
		var availHeight = Math.max(50, Math.min(618, $WH.g_getWindowSize().h - 122 - captionExtraHeight));

		if (video.id) {
			desiredScale = Math.min(772 / video.width, 618 / video.height);
			scale = Math.min(772 / video.width, availHeight / video.height)
		}
		else
			desiredScale = scale = 1;

		if (desiredScale > 1)
			desiredScale = 1;

		if (scale > 1)
			scale = 1;

		prevImgWidth  = Math.round(scale * video.width);
		prevImgHeight = Math.round(scale * video.height);
		var M = Math.max(480, prevImgWidth);

		Lightbox.setSize(M + 20, prevImgHeight + 116 + captionExtraHeight);

		if (captionExtraHeight) {
			prevImgDiv.firstChild.width = prevImgWidth;
			prevImgDiv.firstChild.height = prevImgHeight;
		}
	}

	function render(resizing) {
		if (resizing && (scale == desiredScale) && $WH.g_getWindowSize().h > container.offsetHeight)
			return;

		container.style.visibility = 'hidden';

		var resized = (video.width > 772 || video.height > 618);

		computeDimensions(0);

		// Aowow - /uploads/videos/ not seen on server
		// var url = g_staticUrl + '/uploads/videos/' + (video.pending ? 'pending' : 'normal') + '/' + video.id + '.jpg';
		var url = video.url;

		var html = '<img src="' + url + '" width="' + prevImgWidth + '" height="' + prevImgHeight + '"';
		html += '>';

		prevImgDiv.innerHTML = html;

		if (!resizing) {
		 // Aowow - /uploads/videos/ not seen on server
		 // aOriginal.href = g_staticUrl + '/uploads/videos/' + (video.pending ? 'pending' : 'normal') + '/' + video.id + '.jpg';
			aOriginal.href = $WH.sprintf(vi_siteurls[video.videoType], video.videoId);;
			var hasFrom = video.date && video.user;
			if (hasFrom) {
				var
					postedOn = new Date(video.date),
					elapsed  = (g_serverTime - postedOn) / 1000;

				var a = divFrom.firstChild.childNodes[1];
				a.href = '?user=' + video.user;
				a.innerHTML = video.user;

				var T = divFrom.firstChild.childNodes[3];
				$WH.ee(T);
				g_formatDate(T, elapsed, postedOn);

				divFrom.firstChild.style.display = '';
			}
			else
				divFrom.firstChild.style.display = 'none';

			divFrom.style.display = (hasFrom ? '' : 'none');

			if (Locale.getId(true) != LOCALE_ENUS && video.caption)
				video.caption = '';

			var hasCaption = (video.caption != null && video.caption.length);
			if (hasCaption) {
				var html = '';
				if (hasCaption)
					html += '<span class="screenshotviewer-caption"><b>' + Markup.toHtml(video.caption, { mode: Markup.MODE_SIGNATURE }) + '</b></span>';

				spCaption.innerHTML = html;
			}
			else
				spCaption.innerHTML = "<i class='q0'>NULL</i>";

			divCaption.id = 'title2-' + video.id;

			aEdit.onclick  = vim_ShowEdit.bind(aEdit, video, true);
			aClear.onclick = vim_Clear.bind(aClear, video, true);

			if (video.next !== undefined) {
				aPrev.style.display  = aNext.style.display = '';
				aCover.style.display = 'none';
			}
			else {
				aPrev.style.display  = aNext.style.display = 'none';
				aCover.style.display = '';
			}
		}

		Lightbox.reveal();

		if (spCaption.offsetHeight > 18)
			computeDimensions(spCaption.offsetHeight - 18);

		container.style.visibility = 'visible';
	}

	function nextVideo() {
		if (video.next !== undefined)
			video = vim_videoData[video.next];

		onRender();
	}

	function prevVideo() {
		if (video.prev !== undefined)
			video = vim_videoData[video.prev];

		onRender();
	}
	function onResize() {
		render(1);
	}

	function onHide() {
		aApprove.onclick = aMakeSticky.onclick = aDelete.onclick = null;
		cancelImageLoading();
	}

	function onShow(dest, first, opt) {
		video     = opt;
		container = dest;

		if (first) {
			dest.className = 'screenshotviewer';

			screen = $WH.ce('div');
			screen.className = 'screenshotviewer-screen';

			aPrev = $WH.ce('a');
			aNext = $WH.ce('a');
			aPrev.className = 'screenshotviewer-prev';
			aNext.className = 'screenshotviewer-next';
			aPrev.href = 'javascript:;';
			aNext.href = 'javascript:;';

			var foo = $WH.ce('span');
			$WH.ae(foo, $WH.ce('b'));
			$WH.ae(aPrev, foo);
			var foo = $WH.ce('span');
			$WH.ae(foo, $WH.ce('b'));
			$WH.ae(aNext, foo);

			aPrev.onclick = prevVideo;
			aNext.onclick = nextVideo;

			aCover = $WH.ce('a');
			aCover.className = 'screenshotviewer-cover';
			aCover.href = 'javascript:;';
			aCover.onclick = Lightbox.hide;

			var foo = $WH.ce('span');
			$WH.ae(foo, $WH.ce('b'));
			$WH.ae(aCover, foo);
			$WH.ae(screen, aPrev);
			$WH.ae(screen, aNext);
			$WH.ae(screen, aCover);

			var _div = $WH.ce('div');
			_div.className = 'text';
			h2Name = $WH.ce('h2');
			h2Name.className = 'first';
			$WH.ae(h2Name, $WH.ct(video.name));
			$WH.ae(_div, h2Name);
			$WH.ae(dest, _div);

			prevImgDiv = $WH.ce('div');
			$WH.ae(screen, prevImgDiv);

			$WH.ae(dest, screen);

			var _div = $WH.ce('div');
			_div.style.paddingTop = '6px';
			_div.style.cssFloat = _div.style.styleFloat = 'right';
			_div.className = 'bigger-links';

			aApprove = $WH.ce('a');
			aApprove.href = 'javascript:;';
			$WH.ae(aApprove, $WH.ct('Approve'));
			$WH.ae(_div, aApprove);

			spApprove = $WH.ce('span');
			spApprove.style.display = 'none';
			$WH.ae(spApprove, $WH.ct('Approve'));
			$WH.ae(_div, spApprove);

			$WH.ae(_div, makePipe());

			aMakeSticky = $WH.ce('a');
			aMakeSticky.href = 'javascript:;';
			$WH.ae(aMakeSticky, $WH.ct('Make sticky'));
			$WH.ae(_div, aMakeSticky);

			$WH.ae(_div, makePipe());

			aDelete = $WH.ce('a');
			aDelete.href = 'javascript:;';
			$WH.ae(aDelete, $WH.ct('Delete'));
			$WH.ae(_div, aDelete);

			controlsCOPY = _div;

			$WH.ae(dest, _div);

			divFrom = $WH.ce('div');
			divFrom.className = 'screenshotviewer-from';

			var sp = $WH.ce('span');
			$WH.ae(sp, $WH.ct(LANG.lvvideo_from));
			$WH.ae(sp, $WH.ce('a'));
			$WH.ae(sp, $WH.ct(' '));
			$WH.ae(sp, $WH.ce('span'));
			$WH.ae(divFrom, sp);
			$WH.ae(dest, divFrom);

			_div = $WH.ce('div');
			_div.className = 'clear';
			$WH.ae(dest, _div);

			var aClose = $WH.ce('a');
			aClose.className = 'screenshotviewer-close';
			aClose.href = 'javascript:;';
			aClose.onclick = Lightbox.hide;
			$WH.ae(aClose, $WH.ce('span'));
			$WH.ae(dest, aClose);

			aOriginal = $WH.ce('a');
			aOriginal.className = 'screenshotviewer-original';
			aOriginal.href = 'javascript:;';
			aOriginal.target = '_blank';
			$WH.ae(aOriginal, $WH.ce('span'));
			$WH.ae(dest, aOriginal);

			divCaption = $WH.ce('div');
			spCaption = $WH.ce('span');
			spCaption.style.paddingRight = '8px';
			$WH.ae(divCaption, spCaption);

			var sp = $WH.ce('span');
			sp.style.whiteSpace = 'nowrap';
			aEdit = $WH.ce('a');
			aEdit.href = 'javascript:;';
			$WH.ae(aEdit, $WH.ct('Edit'));
			$WH.ae(sp, aEdit);

			$WH.ae(sp, makePipe());

			aClear = $WH.ce('a');
			aClear.href = 'javascript:;';
			$WH.ae(aClear, $WH.ct('Clear'));
			$WH.ae(sp, aClear);
			$WH.ae(divCaption, sp);
			$WH.ae(dest, divCaption);

			_div = $WH.ce('div');
			_div.className = 'clear';
			$WH.ae(dest, _div);
		}
		else {
			$WH.ee(h2Name);
			$WH.ae(h2Name, $WH.ct(video.name));
		}

		onRender();
	}
	function onRender() {
		if (video.pending) {
			aApprove.onclick 	= vim_Approve.bind(video, true);
			aMakeSticky.onclick = vim_Sticky.bind(video, true);
			aDelete.onclick 	= vim_Delete.bind(video, true);
		}
		else {
			aMakeSticky.onclick = vim_Sticky.bind(video, true);
			aDelete.onclick 	= vim_Delete.bind(video, true);
		}
		aApprove.style.display  = video.pending ? '' : 'none';
		spApprove.style.display = video.pending ? 'none' : '';

		if (!video.width || !video.height) {
			if (loadingImage) {
				loadingImage.onload  = null;
				loadingImage.onerror = null;
			}
			else {
				container.className = '';
				lightboxComponents  = [];

				while (container.firstChild) {
					lightboxComponents.push(container.firstChild);
					$WH.de(container.firstChild);
				}
			}

			var lightboxTimer = setTimeout(function () {
				video.width  = 126;
				video.height = 22;

				computeDimensions(0);

				video.width  = null;
				video.height = null;

				var div = $WH.ce('div');
				div.style.margin = '0 auto';
				div.style.width = '126px';

				var img = $WH.ce('img');
				img.src = g_staticUrl + '/images/ui/misc/progress-anim.gif';
				img.width  = 126;
				img.height = 22;

				$WH.ae(div, img);
				$WH.ae(container, div);

				Lightbox.reveal();
				container.style.visiblity = 'visible'
			}, 150);

			loadingImage = new Image();
			loadingImage.onload = (function (vi, timer) {
				clearTimeout(timer);
				vi.width     = this.width;
				vi.height    = this.height;
				loadingImage = null;
				restoreLightbox();
				render()
			}).bind(loadingImage, video, lightboxTimer);

			loadingImage.onerror = (function (timer) {
				clearTimeout(timer);
				loadingImage = null;
				Lightbox.hide();
				restoreLightbox()
			}).bind(loadingImage, lightboxTimer);

			loadingImage.src = (video.url ? video.url : g_staticUrl + '/uploads/videos/' + (video.pending ? 'pending' : 'normal') + '/' + video.id + '.jpg');
		}
		else
			render();
	}

	function cancelImageLoading() {
		if (!loadingImage)
			return;

		loadingImage.onload = null;
		loadingImage.onerror = null;
		loadingImage = null;

		restoreLightbox();
	}

	function restoreLightbox() {
		if (!lightboxComponents)
			return;

		$WH.ee(container);
		container.className = 'screenshotviewer';
		for (var K = 0; K < lightboxComponents.length; ++K)
			$WH.ae(container, lightboxComponents[K]);

		lightboxComponents = null;
	}

	this.show = function (opt) {
		Lightbox.show('videomanager', {
			onShow:   onShow,
			onHide:   onHide,
			onResize: onResize
		}, opt);
	}
};
