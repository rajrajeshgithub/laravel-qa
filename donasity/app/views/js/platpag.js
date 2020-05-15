/* this js controls:
loads pagination tiles and loads tile changes, there will always be max of ten tiles displayed 
loads one team panel and refreshes one team panel, a team panel may consist of 10.15.20.25..50..(multiples of 5) fundraisers. this is variable for desktop, forced to 10 per panel for mobile
links for "<<" "PREV" "NEXT" or ">>" are displayed or hidden, based on whether they are needed
"PREV" puts focus back one tile (& 1 panel of teams refreshes)
"NEXT" puts focus forward one tile (& 1 panel of teams refreshes)
"<<" retreats one set 10 of tiles, & loads team panel of last displayed tile 
">>" advances one set 10 of tiles, & loads team panel of first displayed tile 
*/

function setactx(varx)
{
	document.getElementById('actx').value=varx;
}
function nextPageCT(varx,mob){  /* next and prev links on team panel */
	var currentpage 	= document.getElementById('currentpagenum').value;
	var lastsequenc		= document.getElementById('lastInSequence').value;	
	if(mob==2){var firstseqtyle	= document.getElementById('pagetylemob1').text;}	//since last tile could be ragged end, determine actual first tile this way
	else{var firstseqtyle	= document.getElementById('pagetyle1').text;}
	
	var currentpagenum	= parseFloat(currentpage);
	var lastsequencenum	= parseFloat(lastsequenc);
	
	if(varx==1){var desiredpagex	= (currentpagenum-1);}
	if(varx==2){var desiredpagex	= (currentpagenum+1);}
	
	if(desiredpagex > lastsequencenum){
		setactx(999999); //retile meter forward a set
		paginateTeamPanelLoad(2,mob,0); // don't trip color here because 999999 will reload func again with 888888	
		
	}
	else{
		if(desiredpagex<firstseqtyle){
			setactx(999999); //retile meter backwards a set
			paginateTeamPanelLoad(1,mob,0); // don't trip color here because 999999 will reload func again with 888888				
		}
		else{ // normal forwarding of tile
			setactx(888888);
			paginateTeamPanelLoad(desiredpagex,mob,1); // 1=trip forced color
		}		
	}	
}
function paginateTeamPanelLoad(desiredpage,mob,forccol){ /*loads actual team panels */
	if(desiredpage!=''){
		var desiredpage			= Math.ceil(desiredpage);
		var currentpagenum 		= document.getElementById('currentpagenum').value;
		var totalteams 			= document.getElementById('totalrecord').value;
		var teamsperpanel 		= document.getElementById('paneliteration').value;
		var teamsperpanelmobile = document.getElementById('mobpaneliteration').value;
		var teamcatnam			= document.getElementById('currteamcat').value;
		var teamsearchterm		= document.getElementById('currteamsrch').value;
		var teamjoincode		= document.getElementById('currteamcode').value;
		
		//var totalpages 		= document.getElementById('lastPage').value;
		// this will differ if desktop says 20 teams per panel, but mobile says 10, so ignore field and calculate instead		
		var teamsperpanel			= parseFloat(teamsperpanel);
		var teamsperpanelmobile		= parseFloat(teamsperpanelmobile);
		var totalteams				= parseFloat(totalteams);
		if(mob==2){totalpages 		= Math.ceil(totalteams/teamsperpanelmobile);}
		else{totalpages 			= Math.ceil(totalteams/teamsperpanel);}		
		
		var lastsequenc			= document.getElementById('lastInSequence').value;
		var actx				= document.getElementById('actx').value;		
		var pageidcolortoload=desiredpage;
		
		var dataArr={};
		dataArr['itmx']		= desiredpage;
		dataArr['pgnm']		= currentpagenum;
		dataArr['ttem']		= totalteams;
		dataArr['perp']		= teamsperpanel;
		dataArr['prpm']		= teamsperpanelmobile;
		dataArr['tcat']		= teamcatnam;
		dataArr['tsch']		= teamsearchterm;
		dataArr['tjin']		= teamjoincode;
		dataArr['tmob']		= mob;

		if(actx=='999999'){  /* retiling tile meter, then load team panel */	
			if(desiredpage==2){  /* load new tile face set, forward ten */
				var lastsequencenum	= parseFloat(lastsequenc);
				settylefacenumbers(lastsequencenum,totalpages,mob);  // lastsequencenum here should actually be what you want for #1 tile face
				var pageidcolortoload=1;
				showPagLink(5);
				if(mob==2){var teampagewanted	= document.getElementById('pagetylemob1').text;}	
				else{var teampagewanted	= document.getElementById('pagetyle1').text;}							
			}
			else if(desiredpage==1){  // load previous tile face set, back ten, then load team panel 	
				if(mob==2){var firstseqtyle	= document.getElementById('pagetylemob1').text;}	// since last tile could be ragged end, determine actual first tile this way
				else{var firstseqtyle	= document.getElementById('pagetyle1').text;}
				var firstseqtylenum = parseFloat(firstseqtyle);
				lastsequencenum=(firstseqtylenum-11); // first tile (11,21,31,41...) minus 10 == the last sequence tile number of previous set, and -1 because loop+1 later			
				settylefacenumbers(lastsequencenum,totalpages,mob);	 // lastsequencenum here should actually be what you want for #1 tile face
				var pageidcolortoload=lastsequencenum; // once previous set of tiles is loaded, this var will help highlight the LAST tile in sequence
				
				if(mob==2){var teampagewanted	= document.getElementById('pagetylemob10').text;}	
				else{var teampagewanted	= document.getElementById('pagetyle10').text;}
			}
			/* now that tile faces refreshed, and proper one highlighted in color, load proper team panel */
			setactx(888888);				
			//proper team to load will be last page in sequence, or first, depending on how tiles were just refaced.		
			paginateTeamPanelLoad(teampagewanted,mob,1);			
		}
		else if(actx=='888888'){   /* loading a panel of teams */	
			$('.pagv2 li a').removeClass('paglnkcolor2');
			if(currentpagenum>0&&totalteams>0&&teamsperpanel>0&&teamsperpanelmobile>0&&desiredpage>0&&mob>0){
				jQuery.ajax({
				type:'POST',	
					cache:false,
					url:SITEURL+'fundraiserdetail/pageChangeTeamPanel',
					data:dataArr,
					contentType: "application/html", 
					dataType: "html", 
					beforeSend:function()
					{
					},
					success:function(res)
					{	
						if(mob==2){
							jQuery('#desktopteampanelmob').html(res); 
							$('#desktopteampanelmob').fadeIn();
						}
						else{
							jQuery('#desktopteampanel').html(res); 
							$('#desktopteampanel').fadeIn();
						}
					},
				});		
				if(mob==2){
					jQuery('#currentpagenum').val(desiredpage);
					if(desiredpage>1){
						showPagLink('1mob');
					}
					if(desiredpage==1){
						showPagLink('2mob');
						resetTileFaceNums(mob); /*reset all tiles to 1-10, "First" button may have been pressed while tiles were disaplying high numbers*/
					}				
					if(desiredpage==totalpages){
						showPagLink('4mob');
					}
					else{
						showPagLink('3mob');
					}
				}
				else{
					jQuery('#currentpagenum').val(desiredpage);
					if(desiredpage>1){
						showPagLink(1);
					}
					if(desiredpage==1){
						showPagLink(2);
						resetTileFaceNums(mob); /*reset all tiles to 1-10, as "First" button may have been pressed while tiles were dispalying high numbers*/
					}				
					if(desiredpage==totalpages){
						showPagLink(4);
					}
					else{
						showPagLink(3);
					}
				}
			}			
		}
	}
	if(forccol==1){ // force colors to tiles, used because if Next/Prev links have just been used, then typical tile jquery was not fired to highlight the focus tile
		if(pageidcolortoload > 10){			
			pagex=Math.round(pageidcolortoload);
			stringnum='abc'+pagex;
			desiredpagexnum	= stringnum.slice(-1);   // nums 12,13,14 or 32,33,34 will be on tile id 2,3,4 respectively 
			if(desiredpagexnum==0){desiredpagexnum=10;}
			forstilcolor(desiredpagexnum,mob);
		}
		else{
			forstilcolor(pageidcolortoload,mob); 
		}		
	}
}
function settylefacenumbers(lasseq,ttlpaqes,mob){ /* lasseq == desired #1 tile face, func only deals with changing numbers displayed on tiles */	

	looptylefaces=0;
	firstnewtyle='';
	if(lasseq<1){lasseq=0;}
	newtylefacenumber=lasseq;

	if(lasseq<ttlpaqes && lasseq > -1){
		for(count=0; count < 10; count++){  /* sets number on tile until 'last page' is reached */
			newtylefacenumber=newtylefacenumber+1;
			if(firstnewtyle==''){firstnewtyle=newtylefacenumber;} /* saving the number of number-one-position tile (11,21,31...) */
			looptylefaces++;
			if(mob==2){anchorid='pagetylemob'+looptylefaces;}
			else{anchorid='pagetyle'+looptylefaces;}
			document.getElementById(anchorid).innerHTML=newtylefacenumber;
			document.getElementById('lastInSequence').value=newtylefacenumber;	/* this will end up being last tile number in this sequence (10,20,30...) */
			if(newtylefacenumber==ttlpaqes){
				break;
			}
		}		
		if(looptylefaces<10){   /* if last page was say "23" then need to blank the faces of the last 7 tiles */
			for(count=looptylefaces; count < 10; count++){
				looptylefaces++;
				if(mob==2){anchorid='pagetylemob'+looptylefaces;}
				else{anchorid='pagetyle'+looptylefaces;}
				document.getElementById(anchorid).innerHTML='&nbsp;&nbsp;';
			}
		}
		/* since tiles >> and << trip jquery color clear, will use a second class to color tile */
		if(firstnewtyle>0){
			// this is only point where able to tell new tile 1 to be highlighted color
		}		
	}
}
function showPagLink(varx){ /* some divs are shown and hidden at various times */
	if(varx=='1'){document.getElementById('previdanch').style.visibility="visible";}
	if(varx=='2'){document.getElementById('previdanch').style.visibility="hidden";}
	if(varx=='3'){document.getElementById('nextdiv').style.visibility="visible";}
	if(varx=='4'){document.getElementById('nextdiv').style.visibility="hidden";}
	if(varx=='1mob'){document.getElementById('previdanchmob').style.visibility="visible";}
	if(varx=='2mob'){document.getElementById('previdanchmob').style.visibility="hidden";}
	if(varx=='3mob'){document.getElementById('nextdivmob').style.visibility="visible";}
	if(varx=='4mob'){document.getElementById('nextdivmob').style.visibility="hidden";}
	if(varx=='5'){
		document.getElementById('pagetylexxx').style.visibility="visible";
		document.getElementById('pagetylefff').style.visibility="visible";
	}
	if(varx=='5mob'){
		document.getElementById('pagetylemobxxx').style.visibility="visible";
		document.getElementById('pagetylemobfff').style.visibility="visible";
	}
	if(varx=='6'){
		document.getElementById('pagetylexxx').style.visibility="hidden";
		document.getElementById('pagetylefff').style.visibility="hidden";
	}
	if(varx=='6mob'){
		document.getElementById('pagetylemobxxx').style.visibility="hidden";
		document.getElementById('pagetylemobfff').style.visibility="hidden";
	}
	
}
function resetTileFaceNums(mob){ /* simple reset tile faces to 1-10 */
	for(countx=1; countx < 11; countx++){
		if(mob==2){anchorid='pagetylemob'+countx;}
		else {anchorid='pagetyle'+countx;}
		document.getElementById(anchorid).innerHTML=countx;
	}
	document.getElementById('lastInSequence').value=10;
}
function forstilcolor(tylenum,mob){	
	if(mob==2){
		$('.paglnksiz').removeClass('paglnkcolor');
		$('.paglnksiz').addClass('paglnkwhite');// make all tiles white
		xtileid='#pagetylemob'+tylenum;		
		$(xtileid).removeClass('paglnkwhite');	// take white off one
		$(xtileid).addClass('paglnkcolor2'); // one is colored
	}
	else{
		$('.paglnksiz').removeClass('paglnkcolor');
		$('.paglnksiz').addClass('paglnkwhite');
		xtileid='#pagetyle'+tylenum;
		$(xtileid).removeClass('paglnkwhite');
		$(xtileid).addClass('paglnkcolor2');
	}	
}