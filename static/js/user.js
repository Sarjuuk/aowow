function us_isOwnProfile() {
	return (typeof g_pageInfo == "object" && g_user.name == g_pageInfo.username)
}
function us_addDescription() {
	var e = $WH.ge("description");
	var c = us_isOwnProfile();
	var f = (e.childNodes.length == 0);
	if (f) {
		if (c) {
			$WH.ae(e, $WH.ct(LANG.user_nodescription2))
		} else {
			$WH.ae(e, $WH.ct(LANG.user_nodescription))
		}
	}
	if (c) {
		var a = $WH.ce("button"),
		g = $WH.ce("div");
		g.className = "pad";
		a.onclick = function () {
			location.href = "?account#community"
		};
		if (f) {
			$WH.ae(a, $WH.ct(LANG.user_composeone))
		} else {
			$WH.ae(a, $WH.ct(LANG.user_editdescription))
		}
		$WH.ae(e, g);
		$WH.ae(e, a)
	}
}
function us_addCharactersTab(e) {
	var c = (us_isOwnProfile() || g_user.roles & U_GROUP_MODERATOR);
	if (!c) {
		var b = [];
		for (var d = 0, a = e.length; d < a; ++d) {
			e[d].pinned = false;
			if (e[d].published && !e[d].deleted) {
				b.push(e[d])
			}
		}
		e = b
	}
	if (e.length) {
		new Listview({
			template: "profile",
			id: "characters",
			name: LANG.tab_characters,
			tabs: tabsRelated,
			parent: "listview-generic",
			onBeforeCreate: Listview.funcBox.beforeUserCharacters,
			sort: [ - 11],
			visibleCols: ["race", "classs", "level", "talents", "gearscore", "achievementpoints"],
			data: e
		})
	}
}
function us_addProfilesTab(e) {
	var c = (us_isOwnProfile() || g_user.roles & U_GROUP_MODERATOR);
	if (!c) {
		var b = [];
		for (var d = 0, a = e.length; d < a; ++d) {
			if (e[d].published && !e[d].deleted) {
				b.push(e[d])
			}
		}
		e = b
	}
	if (e.length) {
		new Listview({
			template: "profile",
			id: "profiles",
			name: LANG.tab_profiles,
			tabs: tabsRelated,
			parent: "listview-generic",
			onBeforeCreate: Listview.funcBox.beforeUserProfiles,
			sort: [ - 11],
			visibleCols: ["race", "classs", "level", "talents", "gearscore"],
			hiddenCols: ["location", "guild"],
			data: e
		})
	}
}
Listview.funcBox.beforeUserComments = function () {
	if (g_user.roles & U_GROUP_COMMENTS_MODERATOR) {
		this.mode = 1;
		this.createCbControls = function (b) {
			var a = $WH.ce("input");
			a.type = "button";
			a.value = "Delete";
			a.onclick = (function () {
				var e = this.getCheckedRows();
				if (!e.length) {
					alert("No comments selected.")
				} else {
					if (confirm("Are you sure that you want to delete " + (e.length == 1 ? "this comment": "these " + e.length + " comments") + "?")) {
						var c = "";
						var d = 0;
						$WH.array_walk(e, function (f) {
							if (!f.purged && !f.deleted) {
								f.deleted = 1;
								if (f.__tr != null) {
									f.__tr.__status.innerHTML = LANG.lvcomment_deleted
								}
								c += f.id + ","
							} else {
								if (f.purged == 1) {++d
								}
							}
						});
						c = $WH.rtrim(c, ",");
						if (c != "") {
							new Ajax("?comment=delete&id=" + c + "&username=" + g_pageInfo.username)
						} (Listview.cbSelect.bind(this, false))();
						if (d > 0) {
							alert("Purged comments cannot be deleted.\n\nA purged comment is a comment that has been\nautomatically removed from the site due to a negative rating.")
						}
					}
				}
			}).bind(this);
			$WH.ae(b, a);
			var a = $WH.ce("input");
			a.type = "button";
			a.value = "Undelete";
			a.onclick = (function () {
				var d = this.getCheckedRows();
				if (!d.length) {
					alert("No comments selected.")
				} else {
					var c = "";
					$WH.array_walk(d, function (e) {
						if (e.deleted) {
							e.deleted = 0;
							if (e.__tr != null) {
								e.__tr.__status.innerHTML = ""
							}
							c += e.id + ","
						}
					});
					c = $WH.rtrim(c, ",");
					if (c != "") {
						new Ajax("?comment=undelete&id=" + c + "&username=" + g_pageInfo.username)
					} (Listview.cbSelect.bind(this, false))()
				}
			}).bind(this);
			$WH.ae(b, a)
		}
	}
	this.customFilter = function (b, a) {
		return (g_user.roles & U_GROUP_COMMENTS_MODERATOR ? a < 250 : !(b.deleted || b.purged || b.removed))
	};
	this.onAfterCreate = function () {
		if (this.nRowsVisible == 0) {
			if (this.tabs.tabs.length == 1) {
				$("#related, #tabs-related, #listview-generic").remove()
			} else {
				if (!this.tabs.tabs[this.tabIndex].hidden) {
					this.tabs.hide(this.tabIndex, 0)
				}
			}
		} else {
			this.updateTabName()
		}
	}
};
Listview.funcBox.beforeUserCharacters = function () {
	var a = (us_isOwnProfile() || (g_user.roles & (U_GROUP_ADMIN | U_GROUP_BUREAU)));
	if (a) {
		this.mode = 1;
		this.createCbControls = function (e, c) {
			if (!c && this.data.length < 15) {
				return
			}
			var b = $WH.ce("input");
			b.type = "button";
			b.value = LANG.button_remove;
			b.onclick = (function () {
				var f = this.getCheckedRows();
				if (!f.length) {
					alert(LANG.message_nocharacterselected)
				} else {
					if (confirm(LANG.confirm_unlinkcharacter)) {
						var d = "";
						$WH.array_walk(f, function (g) {
							d += g.id + ","
						});
						d = $WH.rtrim(d, ",");
						if (d != "") {
							new Ajax("?profile=unlink&id=" + d + "&user=" + g_pageInfo.username)
						}
						this.deleteRows(f)
					}
				}
			}).bind(this);
			$WH.ae(e, b);
			var b = $WH.ce("input");
			b.type = "button";
			b.value = LANG.button_makepub;
			b.onclick = (function () {
				var f = this.getCheckedRows();
				if (!f.length) {
					alert(LANG.message_noprofileselected)
				} else {
					if (confirm(LANG.confirm_publicprofile)) {
						var d = "";
						$WH.array_walk(f, function (g) {
							if (!g.published) {
								g.published = 1;
								if (g.__tr != null) {
									g.__tr.__status.innerHTML = ""
								}
								d += g.id + ","
							}
						});
						d = $WH.rtrim(d, ",");
						if (d != "") {
							new Ajax("?profile=public&id=" + d + "&user=" + g_pageInfo.username + "&bookmarked")
						} (Listview.cbSelect.bind(this, false))()
					}
				}
			}).bind(this);
			$WH.ae(e, b);
			var b = $WH.ce("input");
			b.type = "button";
			b.value = LANG.button_makepriv;
			b.onclick = (function () {
				var f = this.getCheckedRows();
				if (!f.length) {
					alert(LANG.message_noprofileselected)
				} else {
					if (confirm(LANG.confirm_privateprofile)) {
						var d = "";
						$WH.array_walk(f, function (g) {
							if (g.published) {
								g.published = 0;
								if (g.__tr != null) {
									g.__tr.__status.innerHTML = LANG.privateprofile
								}
								d += g.id + ","
							}
						});
						d = $WH.rtrim(d, ",");
						if (d != "") {
							new Ajax("?profile=private&id=" + d + "&user=" + g_pageInfo.username + "&bookmarked")
						} (Listview.cbSelect.bind(this, false))()
					}
				}
			}).bind(this);
			$WH.ae(e, b);
			var b = $WH.ce("input");
			b.type = "button";
			b.value = LANG.button_pin;
			b.onclick = (function () {
				var f = this.getCheckedRows();
				if (!f.length) {
					alert(LANG.message_nocharacterselected)
				} else {
					if (f.length > 1) {
						alert(LANG.message_toomanycharacters)
					} else {
						if (confirm(LANG.confirm_pincharacter)) {
							var d = [];
							$WH.array_walk(f, function (g) {
								d.push(g.id)
							});
							$WH.array_walk(this.data, function (g) {
								g.pinned = ($WH.in_array(d, g.id) != -1);
								if (g.__tr != null) {
									var h = $WH.gE(g.__tr, "a")[1];
									h.className = (g.pinned ? "icon-star-right": "")
								}
							});
							d = d.join(",");
							if (d != "") {
								new Ajax("?profile=pin&id=" + d + "&user=" + g_pageInfo.username)
							} (Listview.cbSelect.bind(this, false))()
						}
					}
				}
			}).bind(this);
			$WH.ae(e, b);
			var b = $WH.ce("input");
			b.type = "button";
			b.value = LANG.button_unpin;
			b.onclick = (function () {
				var f = this.getCheckedRows();
				if (!f.length) {
					alert(LANG.message_nocharacterselected)
				} else {
					if (confirm(LANG.confirm_unpincharacter)) {
						var d = [];
						$WH.array_walk(f, function (g) {
							d.push(g.id)
						});
						$WH.array_walk(this.data, function (g) {
							g.pinned = ($WH.in_array(d, g.id) == -1);
							if (g.__tr != null) {
								var h = $WH.gE(g.__tr, "a")[1];
								h.className = (g.pinned ? "icon-star-right": "")
							}
						});
						d = d.join(",");
						if (d != "") {
							new Ajax("?profile=unpin&id=" + d + "&user=" + g_pageInfo.username)
						} (Listview.cbSelect.bind(this, false))()
					}
				}
			}).bind(this);
			$WH.ae(e, b);
			if (g_user.roles & (U_GROUP_ADMIN | U_GROUP_BUREAU)) {
				var b = $WH.ce("input");
				b.type = "button";
				b.value = LANG.button_resync;
				b.onclick = (function () {
					var f = this.getCheckedRows();
					if (!f.length) {
						alert(LANG.message_nocharacterselected)
					} else {
						var d = "";
						$WH.array_walk(f, function (h) {
							d += h.id + ","
						});
						d = $WH.rtrim(d, ",");
						if (d != "") {
							var g = $WH.ge("roster-status");
							g.innerHTML = LANG.pr_queue_addqueue;
							g.style.display = "";
							new Ajax("?profile=resync&id=" + d, {
								method: "POST",
								onSuccess: function (j, h) {
									var i = parseInt(j.responseText);
									if (isNaN(i)) {
										alert(LANG.message_resyncerror + i)
									} else {
										if (i < 0 && i != -102) {
											alert(LANG.message_resyncerror + "#" + i)
										}
									}
									pr_updateStatus("profile", g, d, true)
								}
							})
						} (Listview.cbSelect.bind(this, false))()
					}
				}).bind(this);
				$WH.ae(e, b)
			}
		}
	}
};
Listview.funcBox.beforeUserProfiles = function () {
	if (us_isOwnProfile()) {
		this.mode = 1;
		this.createCbControls = function (c, b) {
			if (!b && this.data.length < 15) {
				return
			}
			var a = $WH.ce("input");
			a.type = "button";
			a.value = LANG.button_new;
			a.onclick = function () {
				document.location.href = "?profile&new"
			};
			$WH.ae(c, a);
			var a = $WH.ce("input");
			a.type = "button";
			a.value = LANG.button_delete;
			a.onclick = (function () {
				var e = this.getCheckedRows();
				if (!e.length) {
					alert(LANG.message_noprofileselected)
				} else {
					if (confirm(LANG.confirm_deleteprofile)) {
						var d = "";
						$WH.array_walk(e, function (f) {
							d += f.id + ","
						});
						d = $WH.rtrim(d, ",");
						if (d != "") {
							new Ajax("?profile=delete&id=" + d)
						}
						this.deleteRows(e)
					}
				}
			}).bind(this);
			$WH.ae(c, a);
			var a = $WH.ce("input");
			a.type = "button";
			a.value = LANG.button_makepub;
			a.onclick = (function () {
				var e = this.getCheckedRows();
				if (!e.length) {
					alert(LANG.message_noprofileselected)
				} else {
					if (confirm(LANG.confirm_publicprofile)) {
						var d = "";
						$WH.array_walk(e, function (f) {
							if (!f.published) {
								f.published = 1;
								if (f.__tr != null) {
									f.__tr.__status.innerHTML = ""
								}
								d += f.id + ","
							}
						});
						d = $WH.rtrim(d, ",");
						if (d != "") {
							new Ajax("?profile=public&id=" + d)
						} (Listview.cbSelect.bind(this, false))()
					}
				}
			}).bind(this);
			$WH.ae(c, a);
			var a = $WH.ce("input");
			a.type = "button";
			a.value = LANG.button_makepriv;
			a.onclick = (function () {
				var e = this.getCheckedRows();
				if (!e.length) {
					alert(LANG.message_noprofileselected)
				} else {
					if (confirm(LANG.confirm_privateprofile)) {
						var d = "";
						$WH.array_walk(e, function (f) {
							if (f.published) {
								f.published = 0;
								if (f.__tr != null) {
									f.__tr.__status.innerHTML = LANG.privateprofile
								}
								d += f.id + ","
							}
						});
						d = $WH.rtrim(d, ",");
						if (d != "") {
							new Ajax("?profile=private&id=" + d)
						} (Listview.cbSelect.bind(this, false))()
					}
				}
			}).bind(this);
			$WH.ae(c, a)
		}
	}
};
Listview.funcBox.beforeUserSignatures = function () {
	if (us_isOwnProfile()) {
		this.mode = 1;
		this.createCbControls = function (c, b) {
			if (!b && this.data.length < 15) {
				return
			}
			var a = $WH.ce("input");
			a.type = "button";
			a.value = LANG.button_delete;
			a.onclick = (function () {
				var e = this.getCheckedRows();
				if (!e.length) {
					alert(LANG.message_nosignatureselected)
				} else {
					if (confirm(LANG.confirm_deletesignature)) {
						var d = "";
						$WH.array_walk(e, function (f) {
							d += f.id + ","
						});
						d = $WH.rtrim(d, ",");
						if (d != "") {
							new Ajax("?signature=delete&id=" + d)
						}
						this.deleteRows(e);
						this.resetCheckedRows();
						this.refreshRows()
					}
				}
			}).bind(this);
			$WH.ae(c, a);
			var a = $WH.ce("input");
			a.type = "button";
			a.value = LANG.button_edit;
			a.onclick = (function () {
				var d = this.getCheckedRows();
				if (!d.length) {
					alert(LANG.message_nosignatureselected)
				} else {
					if (d.length > 1) {
						alert(LANG.message_toomanysignatures)
					} else {
						document.location.href = "?signature=" + d[0].id
					}
				}
			}).bind(this);
			$WH.ae(c, a);
			var a = $WH.ce("input");
			a.type = "button";
			a.value = LANG.button_markup;
			a.onclick = (function () {
				var d = this.getCheckedRows();
				if (!d.length) {
					alert(LANG.message_nosignatureselected)
				} else {
					if (d.length > 1) {
						alert(LANG.message_toomanysignatures)
					} else {
						prompt(LANG.prompt_signaturemarkup, "[url=" + this.getItemLink(d[0]) + "][sig=" + d[0].id + "][/url]")
					}
				}
			}).bind(this);
			$WH.ae(c, a);
			var a = $WH.ce("input");
			a.type = "button";
			a.value = LANG.button_link;
			a.onclick = (function () {
				var d = this.getCheckedRows();
				if (!d.length) {
					alert(LANG.message_nosignatureselected)
				} else {
					if (d.length > 1) {
						alert(LANG.message_toomanysignatures)
					} else {
						prompt(LANG.prompt_signaturedirect, "http://" + location.host + "?signature=generate&id=" + d[0].id + ".png")
					}
				}
			}).bind(this);
			$WH.ae(c, a)
		}
	}
};
Listview.extraCols.signature = {
	id: "signature",
	name: LANG.signature,
	before: "name",
	align: "left",
	compute: function (d, e, c) {
		var b = $WH.ce("a");
		b.style.fontFamily = "Verdana, sans-serif";
		b.href = this.getItemLink(d);
		b.rel = "np";
		$WH.ae(b, $WH.ce("img", {
			src: "?signature=generate&id=" + d.id + ".png",
			height: 60,
			width: 468
		}));
		$WH.ae(e, b)
	}
};