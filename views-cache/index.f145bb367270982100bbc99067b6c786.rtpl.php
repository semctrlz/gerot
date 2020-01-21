<?php if(!class_exists('Rain\Tpl')){exit;}?><!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1>Chamados</h1>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item active">Home</li>            
          </ol>
        </div>
      </div>
    </div><!-- /.container-fluid -->
  </section>

  <!-- Main content -->
  <section class="content">

    <!-- Default box -->
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">Tarefas diárias</h3>

        <div class="card-tools">
          <button type="button" class="btn btn-tool" data-card-widget="collapse" data-toggle="tooltip" title="Collapse">
            <i class="fas fa-minus"></i></button>          
        </div>
      </div>
      <div class="card-body">
        <table class="table table-striped table-bordered projects">
          <thead>
              <tr>
                  <th style="width: auto">
                      Tarefas diárias
                  </th>
                  <th style="width: 108px" class="text-center">DOM<br>15/12</th>
                  <th style="width: 108px" class="text-center">SEG<br>16/12</th>
                  <th style="width: 108px" class="text-center">TER<br>17/12</th>
                  <th style="width: 108px" class="text-center">QUA<br>18/12</th>
                  <th style="width: 108px" class="text-center">QUI<br>19/12</th>
                  <th style="width: 108px" class="text-center">SEX<br>20/12</th>
                  <th style="width: 108px" class="text-center">SÁB<br>21/12</th>
              </tr>
              
          </thead>
          <tbody>

            <tr style="height: 80px;">
                <td>
                  <a data-toggle="tooltip" title="Descrição com muitos caracteres para verificar como vemos esse texto!">Nome da tarefa</a>
                </td>
                <td class="text-center">
                  <span class="badge"></span>
                  <div class="btn-group btn-group-xs" style="position: relative;">                    
                    <button class="btn btn-success btn-xs" style="display: none; width: 20px;" title="realizada no prazo" data-data="15/12/19" data-tarefa="1" data-result="ok"><i class="fas fa-check"></i></button>
                    <button class="btn btn-warning btn-xs btn-delete" style="display: none;  width: 20px" title="Realizada com atrazo" data-data="15/12/19" data-tarefa="1" data-result="atrazo"><i class="fas fa-check"></i></button>
                    <button class="btn btn-info btn-xs btn-delete" style="display: none;  width: 20px" title="Inserir observação" data-data="15/12/19" data-tarefa="1" data-result="info"><i class="fas fa-info"></i></button>
                  </div>
                  <div class="btn-group btn-group-xs" style="position: relative;">
                    <button class="btn btn-secondary btn-xs" style="display: none; width: 30px" title="Não aplicável" data-data="15/12/19" data-tarefa="1" data-result="na"><i class="fas fa-slash"></i></button>
                    <button class="btn btn-danger btn-xs" style="display: none; width: 30px" title="Não realizada" data-data="15/12/19" data-tarefa="1" data-result="não"><i class="fas fa-times"></i></button>                    
                  </div>
                </td>

                <td class="text-center">
                  <span class="badge"></span>
                  <div class="btn-group btn-group-xs" style="position: relative;">                    
                    <button class="btn btn-success btn-xs" style="display: none; width: 20px;" title="realizada no prazo" data-data="15/12/19" data-tarefa="1" data-result="ok"><i class="fas fa-check"></i></button>
                    <button class="btn btn-warning btn-xs btn-delete" style="display: none;  width: 20px" title="Realizada com atrazo" data-data="15/12/19" data-tarefa="1" data-result="atrazo"><i class="fas fa-check"></i></button>
                    <button class="btn btn-info btn-xs btn-delete" style="display: none;  width: 20px" title="Inserir observação" data-data="15/12/19" data-tarefa="1" data-result="info"><i class="fas fa-info"></i></button>
                  </div>
                  <div class="btn-group btn-group-xs" style="position: relative;">
                    <button class="btn btn-secondary btn-xs" style="display: none; width: 30px" title="Não aplicável" data-data="15/12/19" data-tarefa="1" data-result="na"><i class="fas fa-slash"></i></button>
                    <button class="btn btn-danger btn-xs" style="display: none; width: 30px" title="Não realizada" data-data="15/12/19" data-tarefa="1" data-result="não"><i class="fas fa-times"></i></button>                    
                  </div>
                </td>

                <td class="text-center">
                  <span class="badge"></span>
                  <div class="btn-group btn-group-xs" style="position: relative;">                    
                    <button class="btn btn-success btn-xs" style="display: none; width: 20px;" title="realizada no prazo" data-data="15/12/19" data-tarefa="1" data-result="ok"><i class="fas fa-check"></i></button>
                    <button class="btn btn-warning btn-xs btn-delete" style="display: none;  width: 20px" title="Realizada com atrazo" data-data="15/12/19" data-tarefa="1" data-result="atrazo"><i class="fas fa-check"></i></button>
                    <button class="btn btn-info btn-xs btn-delete" style="display: none;  width: 20px" title="Inserir observação" data-data="15/12/19" data-tarefa="1" data-result="info"><i class="fas fa-info"></i></button>
                  </div>
                  <div class="btn-group btn-group-xs" style="position: relative;">
                    <button class="btn btn-secondary btn-xs" style="display: none; width: 30px" title="Não aplicável" data-data="15/12/19" data-tarefa="1" data-result="na"><i class="fas fa-slash"></i></button>
                    <button class="btn btn-danger btn-xs" style="display: none; width: 30px" title="Não realizada" data-data="15/12/19" data-tarefa="1" data-result="não"><i class="fas fa-times"></i></button>                    
                  </div>
                </td>

                <td class="text-center">
                  <span class="badge"></span>
                  <div class="btn-group btn-group-xs" style="position: relative;">                    
                    <button class="btn btn-success btn-xs" style="display: none; width: 20px;" title="realizada no prazo" data-data="15/12/19" data-tarefa="1" data-result="ok"><i class="fas fa-check"></i></button>
                    <button class="btn btn-warning btn-xs btn-delete" style="display: none;  width: 20px" title="Realizada com atrazo" data-data="15/12/19" data-tarefa="1" data-result="atrazo"><i class="fas fa-check"></i></button>
                    <button class="btn btn-info btn-xs btn-delete" style="display: none;  width: 20px" title="Inserir observação" data-data="15/12/19" data-tarefa="1" data-result="info"><i class="fas fa-info"></i></button>
                  </div>
                  <div class="btn-group btn-group-xs" style="position: relative;">
                    <button class="btn btn-secondary btn-xs" style="display: none; width: 30px" title="Não aplicável" data-data="15/12/19" data-tarefa="1" data-result="na"><i class="fas fa-slash"></i></button>
                    <button class="btn btn-danger btn-xs" style="display: none; width: 30px" title="Não realizada" data-data="15/12/19" data-tarefa="1" data-result="não"><i class="fas fa-times"></i></button>                    
                  </div>
                </td>

                <td class="text-center">
                  <span class="badge"></span>
                  <div class="btn-group btn-group-xs" style="position: relative;">                    
                    <button class="btn btn-success btn-xs" style="display: none; width: 20px;" title="realizada no prazo" data-data="15/12/19" data-tarefa="1" data-result="ok"><i class="fas fa-check"></i></button>
                    <button class="btn btn-warning btn-xs btn-delete" style="display: none;  width: 20px" title="Realizada com atrazo" data-data="15/12/19" data-tarefa="1" data-result="atrazo"><i class="fas fa-check"></i></button>
                    <button class="btn btn-info btn-xs btn-delete" style="display: none;  width: 20px" title="Inserir observação" data-data="15/12/19" data-tarefa="1" data-result="info"><i class="fas fa-info"></i></button>
                  </div>
                  <div class="btn-group btn-group-xs" style="position: relative;">
                    <button class="btn btn-secondary btn-xs" style="display: none; width: 30px" title="Não aplicável" data-data="15/12/19" data-tarefa="1" data-result="na"><i class="fas fa-slash"></i></button>
                    <button class="btn btn-danger btn-xs" style="display: none; width: 30px" title="Não realizada" data-data="15/12/19" data-tarefa="1" data-result="não"><i class="fas fa-times"></i></button>                    
                  </div>
                </td>

                <td class="text-center">
                  <span class="badge"></span>
                  <div class="btn-group btn-group-xs" style="position: relative;">                    
                    <button class="btn btn-success btn-xs" style="display: none; width: 20px;" title="realizada no prazo" data-data="15/12/19" data-tarefa="1" data-result="ok"><i class="fas fa-check"></i></button>
                    <button class="btn btn-warning btn-xs btn-delete" style="display: none;  width: 20px" title="Realizada com atrazo" data-data="15/12/19" data-tarefa="1" data-result="atrazo"><i class="fas fa-check"></i></button>
                    <button class="btn btn-info btn-xs btn-delete" style="display: none;  width: 20px" title="Inserir observação" data-data="15/12/19" data-tarefa="1" data-result="info"><i class="fas fa-info"></i></button>
                  </div>
                  <div class="btn-group btn-group-xs" style="position: relative;">
                    <button class="btn btn-secondary btn-xs" style="display: none; width: 30px" title="Não aplicável" data-data="15/12/19" data-tarefa="1" data-result="na"><i class="fas fa-slash"></i></button>
                    <button class="btn btn-danger btn-xs" style="display: none; width: 30px" title="Não realizada" data-data="15/12/19" data-tarefa="1" data-result="não"><i class="fas fa-times"></i></button>                    
                  </div>
                </td>

                <td class="text-center">
                  <span class="badge"></span>
                  <div class="btn-group btn-group-xs" style="position: relative;">                    
                    <button class="btn btn-success btn-xs" style="display: none; width: 20px;" title="realizada no prazo" data-data="15/12/19" data-tarefa="1" data-result="ok"><i class="fas fa-check"></i></button>
                    <button class="btn btn-warning btn-xs btn-delete" style="display: none;  width: 20px" title="Realizada com atrazo" data-data="15/12/19" data-tarefa="1" data-result="atrazo"><i class="fas fa-check"></i></button>
                    <button class="btn btn-info btn-xs btn-delete" style="display: none;  width: 20px" title="Inserir observação" data-data="15/12/19" data-tarefa="1" data-result="info"><i class="fas fa-info"></i></button>
                  </div>
                  <div class="btn-group btn-group-xs" style="position: relative;">
                    <button class="btn btn-secondary btn-xs" style="display: none; width: 30px" title="Não aplicável" data-data="15/12/19" data-tarefa="1" data-result="na"><i class="fas fa-slash"></i></button>
                    <button class="btn btn-danger btn-xs" style="display: none; width: 30px" title="Não realizada" data-data="15/12/19" data-tarefa="1" data-result="não"><i class="fas fa-times"></i></button>                    
                  </div>
                </td>             
                
            </tr>


              
          </tbody>
      </table>
      </div>
      <!-- /.card-body -->
      <div class="card-footer">
        Footer
      </div>
      <!-- /.card-footer-->
    </div>
    <!-- /.card -->

  </section>
  <!-- /.content -->
</div>
<!-- /.content-wrapper -->

