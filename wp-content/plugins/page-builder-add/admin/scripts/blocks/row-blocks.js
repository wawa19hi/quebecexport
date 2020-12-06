	( function( $ ) {
        
        $(document).ready(function(){
            var isProActive = 'false';
            var rowBlockNames = {
                'RB-1':{
                    tempname : 1,
                    tempCat:'Header',
                    isPro:false,
                },
                'RB-2':{
                    tempname : 2,
                    tempCat:'Header',
                    isPro:false,
                },
                'RB-3':{
                    tempname : 3,
                    tempCat:'Header',
                    isPro:false,
                },
                'RB-4':{
                    tempname : 4,
                    tempCat:'Header',
                    isPro:false,
                },
                'RB-5':{
                    tempname : 5,
                    tempCat:'Feature',
                    isPro:false,
                },
                'RB-6':{
                    tempname : 6,
                    tempCat:'Feature Text',
                    isPro:false,
                },
                'RB-7':{
                    tempname : 7,
                    tempCat:'Feature',
                    isPro:false,
                },
                'RB-8':{
                    tempname : 8,
                    tempCat:'Feature Text',
                    isPro:false,
                },
                'RB-9':{
                    tempname : 9,
                    tempCat:'Text',
                    isPro:false,
                },
                'RB-10':{
                    tempname : 10,
                    tempCat:'Feature Text',
                    isPro:false,
                },
                'RB-11':{
                    tempname : 11,
                    tempCat:'Pricing',
                    isPro:false,
                },
                'RB-12':{
                    tempname : 12,
                    tempCat:'Pricing',
                    isPro:false,
                },
                'RB-13':{
                    tempname : 13,
                    tempCat:'Pricing',
                    isPro:false,
                },
                'RB-14':{
                    tempname : 14,
                    tempCat:'Pricing',
                    isPro:false,
                },
                'RB-15':{
                    tempname : 15,
                    tempCat:'Pricing',
                    isPro:false,
                },
                'RB-16':{
                    tempname : 16,
                    tempCat:'Testimonial',
                    isPro:false,
                },
                'RB-17':{
                    tempname : 17,
                    tempCat:'Feature',
                    isPro:false,
                },
                'RB-18':{
                    tempname : 18,
                    tempCat:'Footer Call To Action',
                    isPro:false,
                },
                'RB-19':{
                    tempname : 19,
                    tempCat:'Testimonial',
                    isPro:false,
                },
                'RB-20':{
                    tempname : 20,
                    tempCat:'Header',
                    isPro:false,
                },
                'RB-21':{
                    tempname : 21,
                    tempCat:'Call To Action',
                    isPro:false,
                },
                'RB-22':{
                    tempname : 22,
                    tempCat:'Call To Action',
                    isPro:false,
                },
                'RB-23':{
                    tempname : 23,
                    tempCat:'Feature',
                    isPro:false,
                },
                'RB-24':{
                    tempname : 24,
                    tempCat:'Feature',
                    isPro:false,
                },
                'RB-25':{
                    tempname : 25,
                    tempCat:'Testimonial',
                    isPro:false,
                },
                'RB-26':{
                    tempname : 26,
                    tempCat:'Pricing',
                    isPro:false,
                },
                'RB-27':{
                    tempname : 27,
                    tempCat:'Call To Action , Footer',
                    isPro:false,
                },
                'RB-28':{
                    tempname : 28,
                    tempCat:'Call To Action',
                    isPro:false,
                },
                'RB-29':{
                    tempname : 29,
                    tempCat:'Call To Action',
                    isPro:false,
                },
                'RB-30':{
                    tempname : 30,
                    tempCat:'Call To Action',
                    isPro:false,
                },
            };
            $.each(rowBlockNames, function(index,val){
                if (val['isPro'] == true  && isProActive == 'false') {
                    var insertBtn = '<div class="rowBlockProUpdateBtn" data-rowBlockName="'+'protemp'+'"> Pro <i class="fa fa-ban"></i> </div>';
                }else{
                    var insertBtn = '<div class="rowBlockUpdateBtn" data-rowBlockName="'+val['tempname']+'"> Insert <i class="fa fa-download" data-rowBlockName="'+val['tempname']+'" ></i> </div>';
                }

                $('#rowBlocksContainer').append(
                    '<div id="rowBlock" class="rowBlock-'+val['tempname']+' rowBlock template-card">'
                        +'<div id="rowBlock-'+val['tempname']+'" class="tempPrev"> <p id="rowBlock-'+val['tempname']+'"><b>Preview</b></p></div>'
                        +'<label for="rowBlock-'+val['tempname']+'"> <img src="'+pluginURL+'/images/templates/rowBlocks/'+val['tempname']+'.png" data-img_src="https://ps.w.org/page-builder-add/assets/screenshot-'+val['tempname']+'.png" class="card-img rowBlock-'+val['tempname']+'">'
                        +'<p class="card-desc"></p> </label>'
                        +insertBtn
                        +'<span class="block-cats-displayed">'+val['tempCat']+'</span>'
                    +'</div>'
                );
            });

            jQuery('.rowBlocksFilterSelector').on('change', function(){
                var WidgetSearchQuery =  jQuery(this).val();
                jQuery('.rowBlock').hide();
                
                jQuery('.rowBlock:contains("'+WidgetSearchQuery+'")').show();

                if (WidgetSearchQuery == 'All') {
                  jQuery('.rowBlock').show();
                }
            });


            if (isProActive == 'true') {
              $('.nonPremUserNotice').css('display','none');
            }
        });
    }( jQuery ) );
