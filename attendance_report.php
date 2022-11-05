<?php include 'db_connect.php' ?>
<div class="container-fluid">
	<div class="col-lg-12">
		<div class="card">
			<div class="card-header"><b>Attendance Report</b></div>
			<div class="card-body">
				<form id="manage-attendance">
					<input type="hidden" name="id" value="">
					<div class="row justify-content-center">
						<label for="" class="mt-2">Class per Subjects</label>
						<div class="col-sm-4">
				            <select name="class_subject_id" id="class_subject_id" class="custom-select select2 input-sm">
				                <option value=""></option>
				                <?php
				                $class = $conn->query("SELECT cs.*,concat(co.course,' ',c.level,'-',c.section) as `class`,s.subject,f.name as fname FROM class_subject cs inner join `class` c on c.id = cs.class_id inner join courses co on co.id = c.course_id inner join faculty f on f.id = cs.faculty_id inner join subjects s on s.id = cs.subject_id ".($_SESSION['login_faculty_id'] ? " where f.id = {$_SESSION['login_faculty_id']} ":"")." order by concat(co.course,' ',c.level,'-',c.section) asc");
				                while($row=$class->fetch_assoc()):
				                ?>
				                <option value="<?php echo $row['id'] ?>" data-cid="<?php echo $row['id'] ?>" <?php echo isset($class_subject_id) && $class_subject_id == $row['id'] ? 'selected' : '' ?>><?php echo $row['class'].' '.$row['subject']. ' [ '.$row['fname'].' ]' ?></option>
				                <?php endwhile; ?>
				            </select>
						</div>
						<label for="" class="mt-2">Month of</label>
						<div class="col-sm-3">
							<input type="month" name="doc" id="doc" value="<?php echo date('Y-m') ?>" class="form-control">
						</div>
						<div class="col-sm-2">
							<button class="btn  btn-primary" type="button" id="filter">Filter</button>
						</div>
					</div>
					<hr>
					<div class="row">
						<div class="col-md-12" id='att-list'>
							<center><b><h4><i>Please Select Class First.</i></h4></b></center>
						</div>
						<div class="col-md-12"  style="display: none" id="submit-btn-field">
							<center>
								<button class="btn btn-success btn-sm col-sm-3" type="button" id="print_att"><i class="fa fa-print"></i> Print</button>
							</center>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>
<div id="table_clone" style="display: none">
	<table width="100%">
		<tr>
			<td width="50%">
				<p>Course: <b class="course"></b></p>
				<p>Subject: <b class="subject"></b></p>
				<p>Total Days of Classes: <b class="noc"></b></p>
			</td>
			<td width="50%">
				<p>Class: <b class="class"></b></p>
				<p>Month of: <b class="doc"></b></p>
			</td>
		</tr>
	</table>
	<table class='table table-bordered table-hover att-list'>
		<thead>
			<tr>
				<th class="text-center" width="5%">#</th>
				<th width="20%">Student</th>
				<th>Present</th>
				<th>Late</th>
				<th>Absent</th>
			</tr>
		</thead>
		<tbody>
		</tbody>
	</table>
</div>
<div id="chk_clone" style="display: none">
	<div class="d-flex justify-content-center chk-opts">
		<div class="form-check form-check-inline">
		  <input class="form-check-input present-inp" type="checkbox" value="1" readonly="">
		  <label class="form-check-label present-lbl">Present</label>
		</div>
		<div class="form-check form-check-inline">
		  <input class="form-check-input absent-inp" type="checkbox" value="0" readonly="">
		  <label class="form-check-label absent-lbl">Absent</label>
		</div>
		<div class="form-check form-check-inline">
		  <input class="form-check-input late-inp" type="checkbox" value="2" readonly="">
		  <label class="form-check-label late-lbl">Late</label>
		</div>
	</div>
</div>
<style>
	.present-inp,.absent-inp,.late-inp,.present-lbl,.absent-lbl,.late-lbl{
		cursor: pointer;
	}
</style>
<noscript>
	<style>
		table.att-list{
			width:100%;
			border-collapse:collapse
		}
		table.att-list td,table.att-list th{
			border:1px solid
		}
		.text-center{
			text-align:center
		}
	</style>
</noscript>
<script>

	$('#filter').click(function(){
		start_load()
		$.ajax({
			url:'ajax.php?action=get_att_report',
			method:'POST',
			data:{class_subject_id:$('#class_subject_id').val(),doc:$('#doc').val()},
			success:function(resp){
				if(resp){
					resp = JSON.parse(resp)
					var _table = $('#table_clone').clone()
					$('#att-list').html('')
					$('#att-list').append(_table)
					var _type = ['Absent','Present','Late'];
					var data = !!resp.data ? resp.data : [] ;
					var record = !!resp.record ? resp.record :[];
					var attendance_id = !!resp.attendance_id ? resp.attendance_id : 0;
					if(Object.keys(data).length > 0){
						var i = 1;
						Object.keys(data).map(function(k){
							var name = data[k].name;
							var id = data[k].id;
							var tr = $('<tr></tr>')

							// opts.find('.present-inp').attr({'name':'type['+id+']','id':'present_'+id})
							// opts.find('.absent-inp').attr({'name':'type['+id+']','id':'absent_'+id})
							// opts.find('.late-inp').attr({'name':'type['+id+']','id':'late_'+id})

							// opts.find('.present-lbl').attr({'for':'present_'+id})
							// opts.find('.absent-lbl').attr({'for':'absent_'+id})
							// opts.find('.late-lbl').attr({'for':'late_'+id})
							var present=0;
							var	late=0;
							var	absent=0;
							console.log(Object.keys(record).length)
							// record = JSON.parse(record)
								if(Object.keys(record).length > 0){
								if(record[id].length > 0){
									Object.keys(record[id]).map(i=>{
										if(record[id][i].type == 0)
											absent = parseInt(absent) + 1;
										if(record[id][i].type == 1)
											present = parseInt(present) + 1;
										if(record[id][i].type == 2)
											late = parseInt(late) + 1;
									})
								}  
								}  

							tr.append('<td class="text-center">'+(i++)+'</td>')
							tr.append('<td class="">'+(name)+'</td>')
							var td = '<td class="text-center">';
								td += present;
								td += '</td>';
								td += '<td class="text-center">';
								td += late;
								td += '</td>';
								td += '<td class="text-center">';
								td += absent;
								td += '</td>';
							tr.append(td)

							_table.find('table.att-list tbody').append(tr)
						})
						$('#submit-btn-field').show()
						$('#edit_att').attr('data-id',attendance_id)
					}else{
							var tr = $('<tr></tr>')
							tr.append('<td class="text-center" colspan="5">No data.</td>')
							_table.find('table.att-list tbody').append(tr)
						$('#submit-btn-field').attr('data-id','').hide()
						$('#edit_att').attr('data-id','')
					} 
					$('#att-list').html('')
					_table.find('.course').text(!!resp.details.course ? resp.details.course : '')
					_table.find('.subject').text(!!resp.details.subject ? resp.details.subject : '')
					_table.find('.class').text(!!resp.details.class ? resp.details.class : '')
					_table.find('.doc').text(!!resp.details.doc ? resp.details.doc : '')
					_table.find('.noc').text(!!resp.details.noc ? resp.details.noc : '')

					_table.find('.noc').text(!!resp.details.noc ? resp.details.noc : '')
					$('#att-list').append(_table.html())
					if(Object.keys(record).length > 0){
						Object.keys(record).map(k=>{
							// console.log('[name="type['+record[k].student_id+']"][value="'+record[k].type+'"]')
							$('#att-list').find('[name="type['+record[k].student_id+']"][value="'+record[k].type+'"]').prop('checked',true)
						})
					}
				}
			},
			complete:function(){
				$("input[readonly]").on('keyup keypress change',function(e){
					e.preventDefault()
					return false;
				});
				$('#edit_att').click(function(){
					location.href = 'index.php?page=check_attendance&attendance_id='+$(this).attr('data-id')
				})
				end_load()
			}
		})
	})
	$('#manage-attendance').submit(function(e){
		e.preventDefault()
		start_load()
		$.ajax({
			url:'ajax.php?action=save_attendance',
			method:'POST',
			data:$(this).serialize(),
			success:function(resp){
				if(resp==1){
					  alert_toast("Data successfully saved.",'success')
                        setTimeout(function(){
                            location.reload()
                        },1000)
				}else if(resp ==2){
					  alert_toast("Class already has an attendance record with the slected subject and date.",'danger')
					  end_load();
				}
			}
		})
	})
	$('#print_att').click(function(){
		var _c = $('#att-list').html();
		var ns = $('noscript').clone();
		var nw = window.open('','_blank','width=900,height=600')
		nw.document.write(_c)
		nw.document.write(ns.html())
		nw.document.close()
		nw.print()
		setTimeout(() => {
			nw.close()
		}, 500);
	})
</script>