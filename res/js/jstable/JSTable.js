/**
 * Creates a new instance of <b>JSTable</b>.
 * @param {Object} initObj An object that contains basic table configurations. 
 * This object can have the following set of attributes:
 * <ul>
 * <li><b>parent-html-id</b>: The ID of the container that the table will be appended 
 * to.</li>
 * <li><b>header</b>: A boolean value. If set to true, the table will have headers 
 * for the columns.</li>
 * <li><b>header</b>: A boolean value. If set to true, the table will have footers 
 * for the columns.</li>
 * <li><b>show-row-num</b>: A boolean value. If set to true, the table will show 
 * row numbers.</li>
 * <li><b>paginate</b>: A boolean value. If set to true, the table will include 
 * pagination controls such as enries count per page and page select buttons.</li>
 * <li><b>enable-search</b>: A boolean value. If set to true, A text box for search 
 * will be included with the table that can be used to search in the columns that has the 
 * attribute <b>'search-enabled'</b> set to true.</li>
 * <li><b>selective-search</b>: A boolean value. If search is enabled and this attribute is set to true, 
 * a combobox will be included with the table to select a column to search on.</li>
 * <li><b>lang</b>: An attribute that contains an object for table labels. The object has the 
 * following attributes for the labels:
 * <ul><li><b>show-label</b>: : Used for pagination control which is used to select how 
 * many rows to show in each page.</li>
 * <ul><li><b>no-data-label</b>: A label that will show if no data can be shown.</li>
 * <ul><li><b>search-label</b>: A label that will be shown along side search textbox.</li>
 * <ul><li><b>select-col-label</b>: A label that will be shown along side the combobox 
 * that is used to select search column.</li>
 * <ul><li><b>print-label</b>: A label to show in the print button.</li>
 * </ul>
 * </li>
 * </ul>
 * @param {Array} cols An array that contains objects. Each object represents one 
 * column. Each object can have the following attributes:
 * <ul>
 * <li><b>key</b>: A unique name for the column. The key is used in case of adding new 
 * row to the table.</li>
 * <li><b>title</b>: A text to show in the header and the footer of the column.</li>
 * <li><b>width</b>: The width of the column in percentage.</li>
 * <li><b>sortable</b>: A boolean value. If set to true, the rows of the column will 
 * be sortable. Default is false.</li>
 * <li><b>search-enabled</b>: A boolean value. if set to true, the user will be able to search 
 * the data on the column. Default is false.</li>
 * <li><b>printable</b>: A boolean value. If set to true, The column will appear in case 
 * of printing. Default is true.</li>
 * </ul>
 * @param {Array} data An initial array of objects. 
 * @constructor
 * @returns {JSTable} An instance of the class.
 */
function JSTable(initObj={},cols=[],data=[]){
    var inst = this;
    Object.defineProperty(this,'obj',{
        value:{},
        enumerable:false,
        configurable:false,
        writable:false
    });
    this.obj['name'] = JSTable.name;
    Object.defineProperties(this.obj,{
        paginate:{
            value:initObj.paginate
        },
        'parent-html-id':{
            value:initObj['parent-html-id']
        },
        printable:{
            value:initObj.printable
        },
        'show-row-num':{
            value:initObj['show-row-num']
        },
        'enable-search':{
            value:initObj['enable-search']
        },
        'selective-search':{
            value:initObj['selective-search']
        },
        header:{
            value:initObj.header
        },
        footer:{
            value:initObj.footer
        },
        attach:{
            value:initObj.attach
        },
        'events':{
            value:{}
        }
    });
    Object.defineProperties(this.obj.events,{
        onrowadded:{
            value:null,
            writable:true,
            configurable:true
        },
        onrowremoved:{
            value:null,
            writable:true,
            configurable:true
        },
        oncoladded:{
            value:null,
            writable:true,
            configurable:true
        },
        afterrowstored:{
            value:function(){
                this.datatable.log('JSTable.afterrowstored: Checking if stored row is hidden','info');
                if(this['new-row'].hidden === false){
                    this.datatable.log('JSTable.afterrowstored: Not hidden','info');
                    this.datatable.log('JSTable.afterrowstored: Getting number of rows per page','info');
                    var rowsPerPage = this.datatable.rowsPerPage();
                    this.datatable.log('JSTable.afterrowstored: Rows per page = '+rowsPerPage,'info');
                    if(rowsPerPage !== Infinity){
                        this.datatable.log('JSTable.afterrowstored: Getting number of visible rows.','info');
                        var vRows = this.datatable.visibleRows();
                        this.datatable.log('JSTable.afterrowstored: Number of visible rows = '+vRows,'info');
                        if(vRows < rowsPerPage){
                            this.datatable.log('JSTable.afterrowstored: Number of visible rows < rows per page. Add extra row to UI.','info');
                            var tr = document.createElement('tr');
                            for(var x = 0 ; x < this.columns.length ; x++){
                                var col = this.datatable.getColumn(x);
                                var cell = document.createElement('td');
                                if(col.printable === false){
                                    cell.className = 'no-print';
                                }
                                if(col.hidden === true){
                                    cell.className = cell.className + ' hidden';
                                }
                                if(col.type === 'boolean'){
                                    var checkbox = document.createElement('input');
                                    checkbox.type = 'checkbox';
                                    checkbox.col = x;
                                    checkbox.row = this['new-row']['row-index'];
                                    checkbox.table = this.datatable;
                                    checkbox.checked = this['new-row'][col.key];
                                    checkbox.onchange = function(){
                                        this.table.set(this.col,this.row,this.checked);
                                    };
                                    cell.appendChild(checkbox);
                                }
                                else{
                                    cell.innerHTML = this['new-row'][col.key];
                                }
                                tr.appendChild(cell);
                            }
                            this.datatable.t_body.appendChild(tr);
                            this.datatable.log('JSTable.afterrowstored: Row added.','info');
                            this.datatable.log('JSTable.afterrowstored: Checking rows number','info');
                            if(this.datatable.rows() === 1){
                                this.datatable.log('JSTable.afterrowstored: It is one. Calling the function \'validateDataState()\'','info');
                                this.datatable.validateDataState();
                            }
                            this.datatable.log('JSTable.afterrowstored: Finished. Return Back','info');
                        }
                        else{
                            this.datatable.log('JSTable.afterrowstored: Calling the function \'afterrowcountchanged\'','info');
                            this.afterrowcountchanged();
                            this.datatable.log('JSTable.afterrowstored: Finished. Return Back','info');
                        }
                    }
                    else{
                        this.datatable.log('JSTable.afterrowstored: Creating tr element','info');
                        var tr = document.createElement('tr');
                        for(var x = 0 ; x < this.columns.length ; x++){
                            this.datatable.log('JSTable.afterrowstored: Calling the function \'getColumn('+x+')\'','info');
                            var col = this.datatable.getColumn(x);
                            this.datatable.log('JSTable.afterrowstored: Creating td element.','info');
                            var cell = document.createElement('td');
                            if(col.printable === false){
                                this.datatable.log('JSTable.afterrowstored: Column not printable','info');
                                cell.className = 'no-print';
                            }
                            if(col.hidden === true){
                                this.datatable.log('JSTable.afterrowstored: Hidden column','info');
                                cell.className = cell.className + ' hidden';
                            }
                            if(col.type === 'boolean'){
                                var checkbox = document.createElement('input');
                                checkbox.type = 'checkbox';
                                checkbox.col = x;
                                checkbox.table = this.datatable;
                                checkbox.row = this['new-row']['row-index'];
                                checkbox.checked = this['new-row'][col.key];
                                checkbox.onchange = function(){
                                    this.table.set(this.col,this.row,this.checked);
                                };
                                cell.appendChild(checkbox);
                            }
                            else{
                                cell.innerHTML = this['new-row'][col.key];
                            }
                            this.datatable.log('JSTable.afterrowstored: Appending cell to row','info');
                            tr.appendChild(cell);
                        }
                        this.datatable.log('JSTable.afterrowstored: Appending row to table body.','info');
                        this.datatable.log(tr.children);
                        this.datatable.t_body.appendChild(tr);
                        if(this.datatable.rows() === 1){
                            this.datatable.validateDataState();
                        }
                        this.datatable.log('JSTable.afterrowstored: Finished. Return Back','info');
                    }
                }
                //this.datatable.events.afterrowcountchanged();
            },
            writable:false,
            configurable:false,
            enumerable:true
        },
        afterrowremoved:{
            value:function(){
                //update indices
                if(this.datatable.rows() === 0){
                    this.datatable.log('JSTable.afterrowremoved: Table has 0 rows','info');
                    this.datatable.log('JSTable.afterrowremoved: Calling the function \'validateDataState()\'','info');
                    this.datatable.validateDataState();
                    this.datatable.log('JSTable.afterrowremoved: Finished. Return back.','info');
                    return;
                }
                else{
                    this.datatable.log('JSTable.afterrowremoved: Table has more than 0 rows','info');
                    this.datatable.log('JSTable.afterrowremoved: Updating row indices','info');
                    for(var y = this.index ; y < this.datatable.rows() ; y++){
                        if(this.datatable.getData()[y]['row-index'] !== -1){
                            this.datatable.getData()[y]['row-index']--;
                        }
                    }
                    this.datatable.log('JSTable.afterrowremoved: Updating row indices finished','info');
                }
                //update UI
                this.datatable.log('JSTable.afterrowremoved: Checking if pagination is enabled.','info');
                if(this.datatable.isPaginationEnabled()){
                    this.datatable.log('JSTable.afterrowremoved: It is enabled.','info');
                    var pagesCount = this.datatable.pagesCount();
                    var rowsInPage = this.datatable.rowsInPage(pagesCount);
                    if(rowsInPage === this.datatable.rowsPerPage()){
                        //this means last page is fully removed
                        this.datatable.log('JSTable.afterrowremoved: Last page removed.','info');
                        this.datatable.log('JSTable.afterrowremoved: Calling the function \'afterrowcountchanged()\'','info');
                        this.afterrowcountchanged();
                    }
                    else{
                        //eathir it is last page with rows
                        //or a page in the middle
                        //if last page, do nothing.
                        if(pagesCount !== this.datatable.getActivePage()){
                            this.datatable.log('JSTable.afterrowremoved: Page in the middle.','info');
                            //a page in the middle
                            //get a row from next page
                            //add it to the current page.
                            this.afterrowcountchanged();
                        }
                    }
                }
                
                var colsCount = this.datatable.cols();
                var cols = this.datatable.getCols();
                this.datatable.log('JSTable.afterrowremoved: Updating UI','info');
                for(var x = 0 ; x < colsCount ; x++){
                    var col = cols[x];
                    if(col.type === 'boolean'){
                        this.datatable.log('JSTable.afterrowremoved: Col \''+col.key+'\' has a boolean. Changing indices.','info');
                        var rowsCount = this.datatable.visibleRows();
                        for(var y = 0 ; y < rowsCount ; y++){
                            this.datatable.log('JSTable.afterrowremoved: Old row index = '+this.datatable.t_body.children[y].children[col.index].children[0].row,'info');
                            this.datatable.t_body.children[y].children[col.index].children[0].row = y;
                            this.datatable.log('JSTable.afterrowremoved: New row index = '+this.datatable.t_body.children[y].children[col.index].children[0].row,'info');
                        }
                    }
                }
            },
            enumerable:true,
            writable:false,
            configurable:false
        },
        afterrowcountchanged:{
            value:function(){
                this.datatable.log('JSTable.afterrowcountchanged: Checking number of rows per page.','info');
                var rowsPerPage = this.datatable.rowsPerPage();
                this.datatable.log('JSTable.afterrowcountchanged: Rows per page = '+rowsPerPage,'info');
                this.datatable.log('JSTable.afterrowcountchanged: Setting page start row to 0.','info');
                this.datatable.obj.start = 0;
                if(rowsPerPage === Infinity){
                    var fRows = this.datatable.filteredRows();
                    this.datatable.log('JSTable.afterrowcountchanged: Setting page end row to '+fRows,'info');
                    this.datatable.obj.end = fRows;
                }
                else if(rowsPerPage > this.datatable.filteredRows()){
                    var fRows = this.datatable.filteredRows();
                    this.datatable.log('JSTable.afterrowcountchanged: Setting page end row to '+fRows,'info');
                    this.datatable.obj.end = fRows;
                    this.datatable.obj.end = fRows;
                }
                else{
                    this.datatable.log('JSTable.afterrowcountchanged: Setting page end row to '+rowsPerPage,'info');
                    this.datatable.obj.end = rowsPerPage;
                }
                this.datatable.log('JSTable.afterrowcountchanged: Calling the function \'displayPage()\'','info');
                this.datatable.displayPage();
                this.datatable.log('JSTable.afterrowcountchanged: Checking if pagination is enabled.','info');
                if(this.datatable.isPaginationEnabled()){
                    this.datatable.log('JSTable.afterrowcountchanged: It is enabled.','info');
                    this.datatable.log('JSTable.afterrowcountchanged: Getting number of pages','info');
                    var pagesCount = this.datatable.pagesCount();
                    this.datatable.log('JSTable.afterrowcountchanged: Number of pages = '+pagesCount,'info');
                    this.datatable.log('JSTable.afterrowcountchanged: Updating page select buttons','info');
                    while(this.datatable.numbersContainer.children.length !== 0){
                        this.datatable.numbersContainer.removeChild(this.datatable.numbersContainer.children[0]);
                    }
                    var inst = this.datatable;
                    if(pagesCount === 0){
                        pagesCount = 1;
                    }
                    for(var x = 0 ; x < pagesCount ; x++){
                        var button = document.createElement('button');
                        var pageNumber = (x + 1);
                        button.innerHTML = pageNumber;
                        this.datatable.numbersContainer.appendChild(button);
                        if(x === 0){
                            button.className = 'active';
                            this.datatable.obj.active = 0;
                        }
                        button.onclick = function(){
                            var pageNumber = Number.parseInt(this.innerHTML);
                            var start = (pageNumber - 1)*rowsPerPage;
                            var end = start + inst.rowsPerPage();
                            if(end > inst.filteredRows()){
                                end = end - (end - inst.filteredRows());
                            }
                            inst.obj.start = start;
                            inst.obj.end = end;
                            inst.pageNumberContainer.children[2].children[inst.obj.active].className = '';
                            inst.obj.active = pageNumber - 1;
                            inst.pageNumberContainer.children[2].children[inst.obj.active].className = 'active';
                            inst.displayPage();
                        };
                    }
                }
                this.datatable.log('JSTable.afterrowcountchanged: Finished. Return back','info');
            },
            enumerable:true,
            writable:false,
            configurable:false
        }
    });
    this.setLogEnabled(initObj['enable-log']);
    this.log('JSTable: Initializing table.','info');
    this.log('JSTable: Creating containers and basic elements.','info');
    Object.defineProperties(this,{
        container:{
            value:document.createElement('div'),
            enumerable:false,
            writable:false,
            configurable:false
        },
        
        table:{
            value:document.createElement('table'),
            enumerable:false,
            writable:false,
            configurable:false
        },
        col_set:{
            value:document.createElement('colgroup'),
            enumerable:false,
            writable:false,
            configurable:false
        },
        t_controls:{
            value:document.createElement('div'),
            enumerable:false,
            writable:false,
            configurable:false
        },
        t_body:{
            value:document.createElement('tbody'),
            enumerable:false,
            writable:false,
            configurable:false
        },
        noDataRow:{
            value:document.createElement('tr'),
            enumerable:false,
            writable:false,
            configurable:false
        }
    });
    this.table.className = 'datatable';
    this.t_controls.className = 'datatable-controls';
    
    this.log('JSTable: Done.','info');
    this.log('JSTable: Checking if search is enabled.','info');
    if(this.isSearchEnabled()){
        this.log('JSTable: It is enabled. Creating search controls','info');
        Object.defineProperties(this,{
            search_input:{
                value:document.createElement('input'),
                enumerable:false,
                writable:false,
                configurable:false
            },
            search_label:{
                value:document.createElement('label'),
                enumerable:false,
                writable:false,
                configurable:false
            }
        });
        this.log('JSTable: Initializing search event.','info');
        this.search_input.oninput = function(){
            inst.log('JSTable: Search value changed.','info');
            inst.search(this.value);
        };
        
        this.log('JSTable: Finished creating search controls','info');
        this.log('JSTable: Checking if selective search is enabled.','info');
        if(this.isSelectiveSearchEnabled()){
            this.log('JSTable: It is enabled. Creating selective search controls','info');
            Object.defineProperties(this,{
                col_select:{
                    value:document.createElement('select'),
                    enumerable:false,
                    writable:false,
                    configurable:false
                }
            });
            var o = document.createElement('option');
            o.value = 'all';
            o.innerHTML = 'Select Column Name to Search by...';
            o.selected = true;
            this.col_select.appendChild(o);
            this.col_select.onchange = function(){
                if(this.value === 'all'){
                    inst.obj['search-col'] = undefined;
                    inst.log('JSTable: Search col = [All Columns]');
                }
                else{
                    inst.obj['search-col'] = inst.getColumn(this.value);
                    inst.log('JSTable: Search col = ['+inst.obj['search-col']+']');
                }
            };
            this.log('JSTable: Finished creating selective search controls','info');
        }
        else{
            this.log('JSTable: Selective search is disabled.','info');
        }
    }
    else{
        this.log('JSTable: Search is disabled.','info');
    }
    this.log('JSTable: Checking if print is enabled.','info');
    if(this.isPrintable()){
        this.log('JSTable: Printing is enabled.','info');
        this.log('JSTable: Initializing print control.','info');
        Object.defineProperties(this,{
            print_button:{
                value:document.createElement('button'),
                enumerable:false,
                writable:false,
                configurable:false
            }
        });
        this.log('JSTable: Initializing print button onclick event.','info');
        this.print_button.onclick = function(){
            inst.log('JSTable: Print click.','info');
            inst.print(document.dir);
        };
        this.log('JSTable: Initializing table print event.','info');
        this.table.print = function(dir='ltr'){
            var t = document.createElement('table');
            t.innerHTML = this.innerHTML;
            var bodyIndex = inst.hasHeader() ? 2 : 1;
            var footerIndex = inst.hasHeader() ? 3 : 2;
            //remove all body cells
            while(t.children[bodyIndex].children.length !== 0){
                t.children[bodyIndex].removeChild(t.children[bodyIndex].children[0]);
            }
            //check for hidden columns and remove them.
            var hiddenCols = 0;
            for(var x = 0 ; x < inst.cols(); x++){
                var col = inst.getColumn(x);
                //remove non printable columns
                if(col.printable === false){
                    var colGroup = t.children[0].children[col.index - hiddenCols];
                    t.children[0].removeChild(colGroup);

                    if(inst.hasHeader()){
                        var hCell = t.children[1].children[0].children[col.index - hiddenCols];
                        t.children[1].children[0].removeChild(hCell);
                    }
                    if(inst.hasFooter()){
                        var fCell = t.children[footerIndex].children[0].children[col.index - hiddenCols];
                        t.children[footerIndex].children[0].removeChild(fCell);
                    }
                    hiddenCols++;
                }
            }

            //iserting data to the new table
            if(inst.rows() === 0){
                var noDataRow = document.createElement('tr');
                var noDataCell = document.createElement('td');
                noDataCell.colSpan = inst.cols() - hiddenCols;
                noDataCell.innerHTML = inst.noDataRow.children[0].innerHTML;
                noDataRow.appendChild(noDataCell);
                noDataCell.style['text-align'] = 'center';
                t.appendChild(noDataRow);
            }
            else{
                for(var x = 0 ; x < inst.rows() ; x++){
                    var data = inst.getData()[x];
                    if(data.hidden === false || data.hidden === undefined){
                        var row = document.createElement('tr');
                        for(var y = 0 ; y < inst.cols() ; y++){
                            var col = inst.getColumn(y);
                            if(col.printable === true){
                                var cell = document.createElement('td');
                                cell.innerHTML = data[col.key];
                                row.appendChild(cell);
                            }
                        }
                        t.children[bodyIndex].appendChild(row);
                    }
                }
            }
            //create hidden frame fro printing
            var oHiddFrame = document.createElement("iframe");
            oHiddFrame.name = 'print-frame';
            var css = document.createElement("link");
            var doc = document;
            var wndw = window;
            oHiddFrame.domain = document.domain;
            oHiddFrame.onload = function(){
                css.href = "https://rawgit.com/usernane/js-datatable/master/JSTable.css"; 
                css.rel = "stylesheet"; 
                css.type = "text/css";
                wndw.frames['print-frame'].document.body.dir = dir;
                wndw.frames['print-frame'].document.body.appendChild(css);
                wndw.frames['print-frame'].document.body.appendChild(t);
                wndw.frames['print-frame'].document.getElementsByTagName('table')[0].className = 'datatable';
                wndw.frames['print-frame'].document.getElementsByTagName('table')[0].border = "1";
                oHiddFrame.contentWindow.__container__ = this;
                oHiddFrame.contentWindow.onafterprint = function(){
                    doc.body.removeChild(this.__container__);
                };
                oHiddFrame.contentWindow.focus();
                oHiddFrame.contentWindow.print();
            };
            oHiddFrame.style.visibility = "hidden";
            oHiddFrame.style.position = "fixed";
            oHiddFrame.style.right = "0";
            oHiddFrame.style.bottom = "0";
            document.body.appendChild(oHiddFrame);
        };
        this.log('JSTable: Finished initializing print control.','info');
    }
    else{
        this.log('JSTable: Print is disabled.','info');
    }
    this.log('JSTable: Checking if pagination is enabled.','info');
    if(this.isPaginationEnabled()){
        this.log('JSTable: It is enabled. Initializing pagination controls.','info');
        Object.defineProperties(this,{
            rowCountSelect:{
                value:document.createElement('select'),
                enumerable:false,
                writable:false,
                configurable:false
            },
            rowCountSelectLabel:{
                value:document.createElement('label'),
                enumerable:false,
                writable:false,
                configurable:false
            },
            pageNumberContainer:{
                value:document.createElement('div'),
                enumerable:false,
                writable:false,
                configurable:false
            },
            nextPageButton:{
                value:document.createElement('button'),
                enumerable:false,
                writable:false,
                configurable:false
            },
            prevPageButton:{
                value:document.createElement('button'),
                enumerable:false,
                writable:false,
                configurable:false
            },
            numbersContainer:{
                value:document.createElement('div'),
                enumerable:false,
                writable:false,
                configurable:false
            }
        });
        this.pageNumberContainer.className = 'page-controls';
        this.numbersContainer.className = 'page-number-container';
        
        this.log('JSTable: Initializing \'next\' and \'previous\' buttons events.','info');
        
        this.nextPageButton.onclick = function(){
            var rowsCount = inst.rowsToDisplay();
            if(inst.obj.end !== inst.filteredRows()){
                inst.pageNumberContainer.children[2].children[inst.obj.active].className = '';
                inst.obj.active++;
                inst.pageNumberContainer.children[2].children[inst.obj.active].className = 'active';
                inst.obj.start = inst.obj.start + inst.rowsPerPage();
                inst.obj.end = inst.obj.end + rowsCount;
                inst.displayPage();
            }
        };
        this.prevPageButton.onclick = function(){
            if(inst.obj.start !== 0){
                inst.pageNumberContainer.children[2].children[inst.obj.active].className = '';
                inst.obj.active--;
                inst.pageNumberContainer.children[2].children[inst.obj.active].className = 'active';
                inst.obj.end = inst.obj.start;
                inst.obj.start = inst.obj.start - inst.rowsPerPage();
                inst.displayPage();
            }
        };
        
        this.log('JSTable: Initializing rows count select per page.','info');
        var option1 = document.createElement('option');
        option1.innerHTML = '10';
        option1.value = '10';
        option1.setAttribute('selected',true);
        this.rowCountSelect.appendChild(option1);
        var option1 = document.createElement('option');
        option1.innerHTML = '25';
        option1.value = '25';
        this.rowCountSelect.appendChild(option1);
        var option1 = document.createElement('option');
        option1.innerHTML = '50';
        option1.value = '50';
        this.rowCountSelect.appendChild(option1);
        var option1 = document.createElement('option');
        option1.innerHTML = '100';
        option1.value = '100';
        this.rowCountSelect.appendChild(option1);
        this.nextPageButton.innerHTML = '&gt;';
        this.prevPageButton.innerHTML = '&lt;';
        this.rowCountSelect.onchange = function(){
            inst.obj.active = undefined;
            inst.obj.events.afterrowcountchanged();
        };
        this.log('JSTable: Finished initializing pagination controls.','info');
    }
    else{
        this.log('JSTable: Pagination is disabled.','info');
    }
    this.log('JSTable: Checking if table has header or not.','info');
    if(this.hasHeader()){
        this.log('JSTable: It has header.','info');
        this.log('JSTable: Initializing header.','info');
        Object.defineProperty(this,'header',{
            value:{},
            enumerable:false,
            writable:false,
            configurable:false
        });
        Object.defineProperties(this.header,{
            t_header:{
                value:document.createElement('thead'),
                enumerable:false,
                writable:false,
                configurable:false
            },
            t_h_row:{
                value:document.createElement('tr'),
                enumerable:false,
                writable:false,
                configurable:false
            }
        });
        this.log('JSTable: Initializing header finished.','info');
    }
    else{
        this.log('JSTable: It has no header.','info');
    }
    if(this.hasFooter()){
        this.log('JSTable: It has footer.','info');
        this.log('JSTable: Initializing footer.','info');
        Object.defineProperty(this,'footer',{
            valeu:{},
            enumerable:false,
            writable:false,
            configurable:false
        });
        Object.defineProperties(this.footer,{
            t_footer:{
                value:document.createElement('tfoot'),
                enumerable:false,
                writable:false,
                configurable:false
            },
            t_f_row:{
                value:document.createElement('tr'),
                enumerable:false,
                writable:false,
                configurable:false
            }
        });
        this.log('JSTable: Initializing footer finished.','info');
    }
    else{
        this.log('JSTable: It has no footer.','info');
    }
    this.log('JSTable: Finished containers and elements.','info');
    this.log('JSTable: Initializing cols and data arrays.','info');
    Object.defineProperties(this.obj,{
        cols:{
            value:[],
            enumerable:false,
            writable:false,
            configurable:false
        },
        data:{
            value:[],
            enumerable:false,
            writable:false,
            configurable:false
        }
    });
    this.log('JSTable: Finished initializing cols and data arrays.','info');
    this.log('JSTable: Checking attribute \'show-row-num\' value.','info');
    if(this.obj['show-row-num'] === true){
        this.log('JSTable: Value = \'true\'.','info');
        var numCol = {
            title:'#',
            width:4,
            key:'row-index',
            type:'number',
            printable:false
        };
    }
    else{
        this.log('JSTable: Value = \'false\' or something else.','info');
        var numCol = {
            title:'#',
            width:4,
            key:'row-index',
            type:'number',
            hidden:true,
            printable:false
        };
    }
    
    this.log('JSTable: Adding row number column to the table.','info');
    this.addColumn(numCol);
    if(Array.isArray(cols)){
        this.log('JSTable: Adding columns...','info');
        for(var x = 0 ; x < cols.length ; x++){
            this.addColumn(cols[x]);
        }
    }
    else{
        this.log('JSTable: attribute \'ocols\' is not an array.','warning',true);
    }
    if(Array.isArray(initObj.cols)){
        this.log('JSTable: Adding columns...','info');
        for(var x = 0 ; x < initObj.cols.length ; x++){
            this.addColumn(initObj.cols[x]);
        }
    }
    else{
        this.log('JSTable: attribute \'initObj.cols\' is not an array.','warning',true);
    }
    this.log('JSTable: Checking initial dataset','info');
    if(Array.isArray(data)){
        this.log('JSTable: Adding data to the table...','info');
        for(var x = 0 ; x < data.length ; x++){
            this.addRow(data[x]);
        }
    }
    else{
        this.log('JSTable: attribute \'data\' is not an arrar.','warning',true);
    }
    if(Array.isArray(initObj.data)){
        this.log('JSTable: Adding data to the table...','info');
        for(var x = 0 ; x < initObj.data.length ; x++){
            this.addRow(initObj.data[x]);
        }
    }
    else{
        this.log('JSTable: attribute \'initObj.data\' is not an arrar.','warning',true);
    }
    this.container.className = 'table-container';
    this.t_controls.className = 'datatable-controls';
    
    
    this.log('JSTable: Initializing NO DATA cell.','info');
    this.noDataRow.appendChild(document.createElement('td'));
    this.noDataRow.children[0].style['text-align'] = 'center';
    this.log('JSTable: Checking language attribute.','info');
    this.obj.lang = {};
    if(typeof initObj.lang !== 'object'){
        this.log('JSTable: Attribute \'lang\' is not set. Setting to default.','warning');
        this.setShowSelectLabelText(this.obj.lang['show-label']); 
        this.setNoDataText(this.obj.lang['no-data-label']);
        this.setSearchLabel(this.obj.lang['search-label']);
        this.setPrintLabel(this.obj.lang['print-label']);
        this.setSelectColLabel(this.obj.lang['select-col-label']);
    }
    else{
        this.setShowSelectLabelText(initObj.lang['show-label']); 
        this.setNoDataText(initObj.lang['no-data-label']);
        this.setSearchLabel(initObj.lang['search-label']);
        this.setPrintLabel(initObj.lang['print-label']);
        this.setSelectColLabel(initObj.lang['select-col-label']);
    }

    this.table.appendChild(this.col_set);
    if(this.hasHeader()){
        this.table.appendChild(this.header.t_header);
        this.header.t_header.appendChild(this.header.t_h_row);
    }
    this.table.appendChild(this.t_body);
    if(this.hasFooter()){
        this.t_body.appendChild(this.footer.t_footer);
        this.footer.t_footer.appendChild(this.footer.t_f_row);
    }
    this.container.appendChild(this.t_controls);
    this.container.appendChild(this.table);
    if(this.isPaginationEnabled()){
        this.t_controls.appendChild(this.rowCountSelectLabel);
        this.t_controls.appendChild(this.rowCountSelect);
        this.container.appendChild(this.pageNumberContainer);
        this.pageNumberContainer.appendChild(this.prevPageButton);
        this.pageNumberContainer.appendChild(this.nextPageButton);
        this.pageNumberContainer.appendChild(this.numbersContainer);
    }
    if(this.isSelectiveSearchEnabled()){
        this.t_controls.appendChild(this.col_select);
    }
    if(this.isSearchEnabled()){
        this.t_controls.appendChild(this.search_label);
        this.t_controls.appendChild(this.search_input);
    }
    if(this.isPrintable()){
        this.t_controls.appendChild(this.print_button);
    }
    if(this.obj.attach === true){
        this.attach();
    }
    this.validateDataState();
    this.log('JSTable: Initializing completed.','info');
}
Object.defineProperty(JSTable,'SUPPORTED_DATATYPES',{
    value:['boolean','string','number']
});
Object.assign(JSTable.prototype,{
    /**
     * Returns the number of visible rows in a page.
     * @param {Number} pageNum The number of the page (Starting from 1).
     * @returns {Number} The number of visible rows in a page. If pagination is 
     * disabled or the page does not exist, the function will return 0.
     */
    rowsInPage:function(pageNum){
        if(this.isPaginationEnabled()){
            var pagesCount = this.pagesCount();
            if(pageNum >= 1 && pageNum <= pagesCount){
                var rowsPerPage = this.rowsPerPage();
                if(pageNum === pagesCount){
                    this.log('JSTable.rowsInPage: Start position: '+startPos,'info');
                    var retVal = 0;
                    for(var startPos = (pageNum - 1) * rowsPerPage ; startPos < this.rows() ; startPos++){
                        retVal++;
                    }
                    return retVal;
                }
                else{
                    return rowsPerPage;
                }
            }
            else{
                this.log('JSTable.rowsInPage: Page number is not in the range (0,'+pagesCount+'].','warning',true);
            }
        }
        else{
            this.log('JSTable.rowsInPage: Paginatin is disabled.','warning',true);
        }
        return 0;
    },
    /**
     * Returns the name of JavaScript class.
     * @returns {String} The name of the class.
     */
    getName:function(){
        return this.obj.name;
    },
    /**
     * Checks if pagination is enabled or disabled.
     * @returns {Boolean} True if enabled. False otherwise.
     */
    isPaginationEnabled:function(){
        return this.obj.paginate === true;
    },
    /**
     * Checks if print functionality is enabled or not.
     * @returns {Boolean} True if enabled. False otherwise.
     */
    isPrintable:function(){
        return this.obj.printable === true;
    },
    /**
     * Enable or disable logging mode (used for development).
     * @param {Boolean} bool If true is given, logging mode will be enabled. If 
     * any thing else is given, logging mode will be disabled. Default value is 
     * false.
     * @returns {undefined}
     */
    setLogEnabled:function(bool=false){
        if(bool === true){
            this.obj['enable-log'] = true;
            this.log('JSTable.setLogEnabled: Logging mode is enabled.','warning');
        }
        else{
            this.obj['enable-log'] = false;
            this.log('JSTable.setLogEnabled: Logging mode is disabled.','warning',true);
        }
    },
    /**
     * Sets a callback that will be called after row removal.
     * @param {Function} func The callback.
     * @returns {undefined}
     */
    setOnRowRemoved:function(func){
        if(typeof func === 'function'){
            this.obj.events.onrowremoved = func;
            this.log('JSTable.setOnRowRemoved: Callback is added.','info');
        }
        else{
            this.log('JSTable.setOnRowRemoved: Given parameter is not a function.','warning',true);
        }
    },
    /**
     * Print the table.
     * @param {String} dir Printing direction. can be 'ltr' or 'rtl'.
     * @returns {undefined}
     */
    print:function(dir='ltr'){
        if(typeof dir === 'string'){
            dir = dir.toLowerCase();
            if(dir === 'ltr' || dir === 'rtl'){
                this.table.print(dir);
            }
            else{
                this.log('JSTable.print: Invalid print writing direction: '+dir+'. \'rtl\' is used','warning',true);
                this.table.print('ltr');
            }
        }
        else{
            this.log('JSTable.print: Invalid print writing direction: '+dir+'. \'rtl\' is used','warning',true);
            this.table.print('ltr');
        }
    },
    /**
     * Returns the number of currently active page (Starting from 1).
     * @returns {Number} The number of active page. If pagination is not 
     * enabled, the function will return 0.
     */
    getActivePage:function(){
        if(this.isPaginationEnabled()){
            if(this.obj.active !== undefined){
                return this.obj.active + 1;
            }
            this.log('JSTable.getActivePage: \'obj.active\' is undefined. 1 is returned.','warning');
            return 1;
        }
        else{
            this.log('JSTable.getActivePage: Pagination is disabled.','warning',true);
        }
        return 0;
    },
    /**
     * Checks if selective search is enabled or not.
     * @returns {Boolean} True if enabled. False otherwise.
     */
    isSelectiveSearchEnabled:function(){
        return this.obj['selective-search'] === true;
    },
    /**
     * Checks if search is enabled or not.
     * @returns {Boolean} True if enabled. False otherwise.
     */
    isSearchEnabled:function(){
        if(this.obj['enable-search'] === true){
            return true;
        }
        return false;
    },
    /**
     * Remove a row given its index.
     * @param {Number} rowIndex The index of the row.
     * @param {Boolean} removeData If set to false, the row will be only removed 
     * from the GUI which means it can appear again at some point in case of 
     * sorting or searching.
     * @returns {Boolean|Object} An object that contains row data if removed. False 
     * If not removed.
     */
    removeRow:function(rowIndex){
        this.log('JSTable.removeRow: Row index: '+rowIndex,'info');
        this.log('JSTable.removeRow: Checking index validity','info');
        var vRows = this.visibleRows();
        if(rowIndex >= 0 && rowIndex < vRows){
            this.log('JSTable.removeRow: Searching for the row.','info');
            for(var x = 0 ; x < this.rows() ; x++){
                this.log('JSTable.removeRow: Checking index '+x,'info');
                if(this.getData()[x]['row-index'] === rowIndex){
                    this.log('JSTable.removeRow: Row found at x = '+x,'info');
                    this.log('JSTable.removeRow: Removing row from data','info');
                    var rowData = this.obj.data.splice(x,1)[0];
                    this.log(rowData);
                    this.log('JSTable.removeRow: Row Index = '+rowData['row-index'],'info');
                    this.log('JSTable.removeRow: Removing row from UI','info');
                    var tr = this.t_body.children[rowData['row-index']];
                    this.t_body.removeChild(tr);
                    this.obj.events.datatable = this;
                    this.obj.events['row-data'] = rowData;
                    this.obj.events.index = x;
                    this.obj.events.tr = tr;
                    this.log('JSTable.removeRow: Firing afterrowremoved event','info');
                    this.obj.events.afterrowremoved();
                    this.log('JSTable.removeRow: Firing onrowremoved event','info');
                    if(typeof this.obj.events.onrowremoved === 'function'){
                        this.obj.events.onrowremoved();
                        this.log('JSTable.removeRow: Event completed.','info');
                    }
                    else{
                        this.log('JSTable.removeRow: No event is fired.','info');
                    }
                    delete this.obj.events['row-data'];
                    delete this.obj.events.index;
                    delete this.obj.events.tr;
                    this.log('JSTable.removeRow: Returning row data','info');
                    return rowData;
                }
            }
            this.log('JSTable.removeRow: Given row index is not visible in the UI.','info',true);
            return false;
        }
        this.log('JSTable.removeRow: Row index is not in the range [0,'+vRows+')','warning',true);
        return false;
    },
    filteredData:function(){
        var data = [];
        for(var x = 0 ; x < this.rows() ; x++){
            if(this.getData()[x].hidden === false){
                data.push(this.getData()[x]);
            }
        }
        return data;
    },
    /**
     * Returns the number of filtered rows based on search keyword.
     * @returns {Number} The number of filtered rows based on search keyword.
     */
    filteredRows:function(){
        return this.filteredData().length;
    },
    /**
     * Returns the key name of the column that is selected as a search column.
     * @returns {String|undefined} The key name of the column that is selected as a search column. 
     * If no column is selected, the function will return undefined.
     */
    getSearchCol:function(){
        return this.obj['search-col'];
    },
    /**
     * Search the table.
     * @param {String} val The value to search for.
     * @returns {undefined}
     */
    search:function(val){
        this.log('JSTable.search: Searching for \''+val+'\'.','info');
        if(this.isSearchEnabled()){
            if(val !== undefined && val !== ''){
                this.log('JSTable.search: Resetting search attribute \'hidden\' of rows.','info');
                for(var x = 0 ; x < this.rows() ; x++){
                    this.getData()[x].hidden = true;
                }
                this.log('JSTable.search: Resetting Finished.','info');
                var searchCol = this.getSearchCol();
                var regEx = new RegExp(val.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'));
                if(searchCol !== undefined){
                    this.log('JSTable.search: Searching in the column \''+searchCol.key+'\'.','info');
                    for(var y = 0 ; y < this.rows() ; y++){
                        this.log('JSTable.search: Searching row \''+y+'\'.','info');
                        if(regEx.test(this.getData()[y][searchCol.key]) === true){
                            this.log('JSTable.search: A match is found.','info');
                            this.getData()[y].hidden = false;
                        }
                    }
                    this.obj.events.afterrowcountchanged();
                }
                else{
                    var searchCols = this.getSearchColsKeys();
                    if(searchCols.length !== 0){
                        for(var x = 0 ; x < searchCols.length ; x++){
                            this.log('JSTable.search: Searching in the column \''+searchCols[x]+'\'.','info');
                            var totalMatches = 0;
                            for(var y = 0 ; y < this.rows() ; y++){
                                this.log('JSTable.search: Searching row \''+y+'\'.','info');
                                this.log('JSTable.search: Printing row.','info');
                                this.log(this.getData()[y][searchCols[x]]);
                                var testResult = regEx.test(this.getData()[y][searchCols[x]]);
                                this.log('JSTable.search: Printing result.','info');
                                this.log(testResult,'info');
                                this.getData()[y].hidden = !testResult;
                            }
                            this.log('JSTable.search: Total matches: \''+totalMatches+'\'.','info');
                        }
                        this.obj.events.afterrowcountchanged();
                    }
                    else{
                        this.log('JSTable.search: No column has the attribute \'search-enabled\' set to true.','warning',true);
                        for(var x = 0 ; x < this.rows() ; x++){
                            this.getData()[x].hidden = false;
                        }
                    }
                }
            }
            else{
                this.log('JSTable.search: Nothing to search for.','info');
                for(var x = 0 ; x < this.rows() ; x++){
                    this.getData()[x].hidden = false;
                }
                this.obj.events.afterrowcountchanged();
            }
        }
        else{
            this.log('JSTable.search: Search is disabled.','info');
        }
    },
    /**
     * Sets the label that is displayed in the print button.
     * @param {String} label The text to set.
     * @returns {undefined}
     */
    setPrintLabel:function(label){
        if(this.isPrintable()){
            if(label !== undefined){
                this.print_button.innerHTML = label+'';
                this.obj.lang['print-label'] = this.search_label.innerHTML;
            }
            else{
                this.print_button.innerHTML = 'Print Table';
                this.obj.lang['print-label'] = this.print_button.innerHTML;
                this.log('JSTable.setPrintLabel: Undefined is given. Default will be used.','warning');
            }
            this.log('JSTable.sePrintLabel: Label updated to \''+this.obj.lang['print-label']+'\'.','info');
        }
        else{
            this.log('JSTable.sePrintLabel: Print is disabled.','info');
        }
    },
    /**
     * Sets the label that is displayed along side the combobox that is used to 
     * select search column.
     * @param {String} label The text to set.
     * @returns {undefined}
     */
    setSelectColLabel:function(label){
        if(this.isSelectiveSearchEnabled()){
            if(label !== undefined){
                this.col_select.children[0].innerHTML = label+'';
                this.obj.lang['select-col-label'] = this.col_select.children[0].innerHTML;
            }
            else{
                this.col_select.children[0].innerHTML = 'Select a column...';
                this.obj.lang['select-col-label'] = this.col_select.children[0].innerHTML;
                this.log('JSTable.setSelectColLabel: Undefined is given. Default will be used.','warning');
            }
            this.log('JSTable.setSelectColLabel: Label updated to \''+this.obj.lang['select-col-label']+'\'.','info');
        }
        else{
            this.log('JSTable.setSelectColLabel: Selective search is disabled.','info');
        }
    },
    /**
     * Sets the label that is displayed along side the textbox that is used to 
     * search the table.
     * @param {String} label The text to set.
     * @returns {undefined}
     */
    setSearchLabel:function(label){
        if(this.isSearchEnabled()){
            if(label !== undefined){
                this.search_label.innerHTML = label+'';
                this.obj.lang['search-label'] = this.search_label.innerHTML;
            }
            else{
                this.search_label.innerHTML = 'Search:';
                this.obj.lang['search-label'] = this.search_label.innerHTML;
                this.log('JSTable.setSearchLabel: Undefined is given. Default will be used.','warning');
            }
            this.log('JSTable.setSearchLabel: Label updated to \''+this.obj.lang['search-label']+'\'.','info');
        }
        else{
            this.log('JSTable.setSearchLabel: Search is disabled.','info');
        }
    },
    /**
     * Checks if table has data or not. If no data is visible, 
     * display 'no data' cell.
     * @returns {undefined}
     */
    validateDataState:function(){
        try{
            this.log('JSTable.validateDataState: Checking visible rows count and visible columns count.','info');
            var vRows = this.visibleRows();
            var vCols = this.visibleCols();
            this.log('JSTable.validateDataState: Visible rows = '+vRows,'info');
            this.log('JSTable.validateDataState: Visible cols = '+vCols,'info');
            if(vRows === 0 || vCols === 0){
                this.log('JSTable.validateDataState: Nothing is visible. Adding empty cell.','info');
                this.noDataRow.children[0].colSpan = vCols;
                this.t_body.appendChild(this.noDataRow);
            }
            else{
                this.log('JSTable.validateDataState: Has data. Removing empty cell.','info');
                this.t_body.removeChild(this.noDataRow);
            }
        }
        catch(e){
            this.log(e,'error');
        }
    },
    
    setRowVisible:function(index,boolean){
        this.log('JSTable.setRowVisible: Checkig parameter type','info');
        if(typeof boolean === 'boolean'){
            this.log('JSTable.setRowVisible: It is a boolean','info');
            var vRows = this.visibleRows();
            this.log('JSTable.setRowVisible: Checking row index validity.','info');
            if(index >= 0 && index < this.rows()){
                this.log('JSTable.setRowVisible: Valid row index.','info');
                this.log('JSTable.setRowVisible: Searching for the row.','info');
                if(boolean === true){
                    for(var x = 0 ; x < this.rows() ; x++){
                        if(index === x){
                            this.log('JSTable.setRowVisible: Row found. Checking if it is visible.','info');
                            if(this.obj.data[x].hidden === true){
                                this.log('JSTable.setRowVisible: It is not visible. Making it visible.','info');
                                this.obj.data[x].hidden = false;
                                this.obj.data[x]['row-index'] = vRows;
                                this.t_body.appendChild(JSTable.createRow(this.getCols(),this.obj.data[x])); 
                            }
                            else{
                                this.log('JSTable: Row already visible.','info',true);
                            }
                        }
                    }
                }
                else{
                    for(var x = 0 ; x < this.rows() ; x++){
                        if(index === x){
                            this.log('JSTable.setRowVisible: Row found. Checking if it is visible.','info');
                            if(this.obj.data[x].hidden === false || this.obj.data[x].hidden === undefined){
                                this.log('JSTable.setRowVisible: It is visible. hiding it','info');
                                this.obj.data[x].hidden = true;
                                this.t_body.removeChild(this.t_body.children[this.obj.data[x]['row-index']]);
                                this.obj.data[x]['row-index'] = -1;
                                return;
                            }
                            else{
                                this.log('JSTable: Row already hidden.','info',true);
                                return;
                            }
                        }
                    }
                }
                this.log('JSTable.setRowVisible: Calling the functin \'validateDataState()\'.','info');
                this.validateDataState();
                this.log('JSTable.setRowVisible: Finished.','info');
            }
            else{
                this.log('JSTable.setRowVisible: Row index is not in the range [0,'+(this.rows())+')','warning',true);
            }
        }
        else{
            this.log('JSTable.setRowVisible: Given parameter is not a boolean.','error',true);
        }
    },
    /**
     * Sets the label that is displayed along side the combobox that is used to 
     * select how many rows to show in each page.
     * @param {String} label The text to set.
     * @returns {undefined}
     */
    setShowSelectLabelText:function(label){
        if(this.isPaginationEnabled()){
            if(label !== undefined){
                this.rowCountSelectLabel.innerHTML = label+'';
                this.obj.lang['show-label'] = this.rowCountSelectLabel.innerHTML;
            }
            else{
                this.log('JSTable.setShowSelectLabelText: Undefined is given. Default will be used.','warning');
                this.rowCountSelectLabel.innerHTML = 'Show:';
                this.obj.lang['show-label'] = this.rowCountSelectLabel.innerHTML;
            }
            this.log('JSTable.setShowSelectLabelText: Label updated to \''+this.obj.lang['show-label']+'\'.','info');
        }
        else{
            this.log('JSTable.setShowSelectLabelText: Pagination is disabled.','info');
        }
    },
    /**
     * Sets the label that is displayed when no rows are being displayed.
     * @param {String} label The text to set.
     * @returns {undefined}
     */
    setNoDataText:function(label){
        if(label !== undefined){
            this.noDataRow.children[0].innerHTML = label+'';
            this.obj.lang['no-data-label'] = this.noDataRow.children[0].innerHTML;
        }
        else{
            this.noDataRow.children[0].innerHTML = 'NO DATA';
            this.obj.lang['no-data-label'] = this.noDataRow.children[0].innerHTML;
            this.log('JSTable.setNoDataText: Undefined is given. Default will be used.','warning');
        }
        this.log('JSTable.setNoDataText: Label updated to \''+this.obj.lang['no-data-label']+'\'.','info');
    },
    /**
     * A function to call in case selected page is changed (for pagination).
     * @returns {undefined}
     */
    displayPage:function(){
        this.log('JSTable.displayPage: Removing All UI Rows.','info');
        while(this.t_body.children.length !== 0){
            this.log('JSTable.displayPage: Number of remaining rows: '+this.t_body.children.length,'info');
            this.t_body.removeChild(this.t_body.children[0]);
        }
        this.log('JSTable.displayPage: All UI rows removed.','info');
        this.log('JSTable.displayPage: Setting the attribute \'row-index\' to -1','info');
        for(var x = 0 ; x < this.rows() ; x++){
            this.obj.data[x]['row-index'] = -1;
        }
        this.log('JSTable.displayPage: Adding Rows to UI.','info');
        this.log('JSTable.displayPage: Start = '+this.obj.start+', End = '+this.obj.end,'info');
        var rowIndex = 0;
        for(var x = this.obj.start ; x < this.obj.end ; x++){
            if(x < this.rows() && this.obj.end <= this.rows()){
                this.filteredData()[x]['row-index'] = rowIndex;
                var data = this.filteredData()[x];
                this.log('JSTable.displayPage: Adding row '+x+'.','info');
                this.log('JSTable.displayPage: Printing Row','info');
                this.log(data);
                var tr = document.createElement('tr');
                var colsCount = this.cols();
                for(var y = 0 ; y < colsCount ; y++){
                    var col = this.getColumn(y);
                    var cell = document.createElement('td');
                    if(col.printable === false){
                        cell.className = 'no-print';
                    }
                    if(col.hidden === true){
                        cell.className = cell.className + ' hidden';
                    }
                    if(col.type === 'boolean'){
                        var checkbox = document.createElement('input');
                        checkbox.type = 'checkbox';
                        checkbox.col = y;
                        checkbox.row = rowIndex;
                        checkbox.table = this;
                        checkbox.checked = data[col.key];
                        checkbox.onchange = function(){
                            this.table.set(this.col,this.row,this.checked);
                        };
                        cell.appendChild(checkbox);
                    }
                    else if(col.key === 'row-index'){
                        if(this.isPaginationEnabled()){
                            cell.innerHTML = data[col.key] + 1 + ((this.getActivePage() - 1)*this.rowsPerPage()); 
                        }
                        else{
                            cell.innerHTML = data[col.key] + 1;
                        }
                    }
                    else{
                        cell.innerHTML = data[col.key];
                    }
                    tr.appendChild(cell);
                }
                this.t_body.appendChild(tr);
                rowIndex++;
            }
        }
        this.log('JSTable.displayPage: Finished. Returning back.');
    },
    /**
     * Loging function. Used in stage of development.
     * @param {Mixed} message The message to display.
     * @param {String} type The type of the message. it can be 'warning', 'info' 
     * or 'error'.
     * @param {Boolean} force If set to true and the logging is disabled, the 
     * message will be shown.
     * @returns {undefined}
     */
    log:function(message,type='',force=false){
        if(this.obj['enable-log'] === true || force===true){
            if(type==='info'){
                console.info(message);
            }
            else if(type==='warning'){
                console.warn(message);
            }
            else if(type==='error'){
                console.error(message);
            }
            else{
                console.log(message);
            }
        }
    },
    /**
     * Returns the number of pages in the table (Used in case of pagiation).
     * @returns {Number} The number of pages in the table. If pagination is 
     * disabled, the function will return 0. If pagination is enabled and 
     * no rows in the table, the function will also return 0.
     */
    pagesCount:function(){
        var totalRows = this.filteredRows();
        var rowsToDisplay = this.rowsPerPage();
        return Math.ceil(totalRows/rowsToDisplay);
    },
    /**
     * Returns the number of rows per page.
     * @returns {Number} The number of rows per page. The value is taken from 
     * rows per page combobox. If pagination is disabled, the 
     * function will return <b>Infinity</b>.
     */
    rowsPerPage:function(){
        if(this.obj['paginate'] === true){
            var count = Number.parseInt(this.rowCountSelect.value);
            if(!Number.isNaN(count)){
                return count;
            }
            return 10;
        }
        else{
            return Infinity;
        }
    },
    /**
     * Returns the number of rows to display per page.
     * @returns {Number}
     */
    rowsToDisplay:function(){
        if(this.obj.paginate === true){
            var count = Number.parseInt(this.rowCountSelect.value);
            if(this.obj.end !== undefined){
                if((this.obj.end + count) < this.rows()){
                    return count;
                }
                return this.filteredRows() - this.obj.end;
            }
            else{
                return count;
            }
        }
    },
    /**
     * Checks if a column is hidden or not given its index or key.
     * @param {String|Number} colKeyOrIndex The key or the index of the column. 
     * @returns {Boolean} True if the column is hidden. False if not. 
     * If no column is found, the function will return false.
     */
    isColumnHidden:function(colKeyOrIndex){
        if(typeof colKeyOrIndex === 'number'){
            if(colKeyOrIndex >= 0 && colKeyOrIndex < this.cols()){
                return this.obj.cols[colKeyOrIndex].hidden === true;
            }
        }
        else if(typeof colKeyOrIndex === 'string'){
            var cols = this.getCols();
            for(var x = 0 ; x < cols.length ; x++){
                if(cols[x].key === colKeyOrIndex){
                    return cols[x].hidden === true;
                }
            }
        }
        this.log('JSTable.isColumnHidden: Invalid column name or index: '+colKeyOrIndex,'info');
        return false;
    },
    /**
     * Sets the value at the given row and column.
     * @param {String|Number} colIndexOrKey Column key or index.
     * @param {Number} row Row index.
     * @param {Mixed} val The value to set.
     * @returns {Boolean} True if the value is set. False otherwise.
     */
    set:function(colIndexOrKey,row,val){
        this.log('JSTable.set: Updating value at col \''+colIndexOrKey+'\' , row \''+row+'\' to \''+val+'\'','info');
        var rowsCount = this.rows();
        this.log('JSTable.set: Validating row index.','info');
        if(row >= 0 && row < rowsCount){
            this.log('JSTable.set: Checking column index type.','info');
            if(typeof colIndexOrKey === 'number'){
                this.log('JSTable.set: Column index is a number.','info');
                this.log('JSTable.set: Validating column index.','info');
                var colsCount = this.cols();
                if(colIndexOrKey >= 0 && colIndexOrKey < colsCount){
                    var type = typeof val;
                    var col = this.getColumn(colIndexOrKey);
                    var colDatatype = col.type;
                    this.log('JSTable.set: Validating datatype.','info');
                    if(type === colDatatype){
                        for(var x = 0 ; x < rowsCount ; x++){
                            for(var y = 0 ; y < colsCount ; y++){
                                if(this.obj.data[x]['row-index'] === row && y === colIndexOrKey){
                                    var colKey = this.getColumn(colIndexOrKey).key;
                                    this.log('JSTable.set: Updating value at column \''+colKey+'\' row \''+row+'\'.','info');
                                    this.obj.data[x][colKey] = val;
                                    this.log('JSTable.set: Updating UI','info');
                                    var vRows = this.visibleRows();
                                    for(var n = 0 ; n < vRows ; n++){
                                        for(var z = 0 ; z < this.rows() ; z++){
                                            if(this.getData()[x]['row-index'] === n){
                                                if(type === 'string' || type === 'number'){
                                                    this.t_body.children[n].children[col.index].innerHTML = val;
                                                }
                                                else if(type === 'boolean'){
                                                    this.t_body.children[n].children[col.index].children[0].checked = val;
                                                }
                                                break;
                                            }
                                        }
                                    }
                                    return true;
                                }
                            }
                        }
                    }
                    else{
                        this.log('JSTable.set: Invalid column data type: '+type,'warning');
                        this.log('JSTable.set: The column \''+colIndexOrKey+'\' can only accept '+colDatatype,'info');
                    }
                }
                else{
                    this.log('JSTable.set: Invalid column index: '+colIndexOrKey,'info');
                }
            }
            else if(this.hasCol(colIndexOrKey)){
                this.log('JSTable.set: It is a key.','info');
                var type = typeof val;
                var col = this.getColumn(colIndexOrKey);
                var colDatatype = col.type;
                this.log('JSTable.set: Validating col data type.','info');
                if(type === colDatatype){
                    this.log('JSTable.set: Valid data type.','info');
                    for(var x = 0 ; x < rowsCount ; x++){
                        if(this.obj.data[x]['row-index'] === row){
                            this.obj.data[x][colIndexOrKey] = val;
                            var vRows = this.visibleRows();
                            for(var n = 0 ; n < vRows ; n++){
                                for(var z = 0 ; z < this.rows() ; z++){
                                    if(this.getData()[x]['row-index'] === n){
                                        if(type === 'string' || type === 'number'){
                                            this.t_body.children[n].children[col.index].innerHTML = val;
                                        }
                                        else if(type === 'boolean'){
                                            this.t_body.children[n].children[col.index].children[0].checked = val;
                                        }
                                        break;
                                    }
                                }
                            }
                            return true;
                        }
                    }
                }
                else{
                    this.log('JSTable.set: Invalid column data type: '+type,'warning');
                    this.log('JSTable.set: The column \''+colIndexOrKey+'\' can only accept '+colDatatype,'info');
                }
            }
            else{
                this.log('JSTable.set: Invalid column: '+colIndexOrKey,'warning');
            }
        }
        else{
            this.log('JSTable.set: Invalid row index: '+row,'warning');
        }
        return false;
    },
    /**
     * Returns the index of a colum given its key.
     * @param {String} colKey The name of the column key.
     * @returns {Number} The index of the column. If no column is found, the 
     * function will return -1.
     */
    getColIndex:function(colKey){
        var cols = this.getCols();
        for(var x = 0 ; x < cols.length ; x++){
            if(cols[x].key === colKey){
                return cols[x].index;
            }
        }
        this.log('JSTable.getColIndex: No such column: '+colKey);
        return -1;
    },
    /**
     * Sets the title of the column.
     * @param {Number|String} ccolKeyOrIndex The index of the column or its key.
     * @param {String} val The new title.
     * @returns {undefined}
     */
    setColTitle:function(colKeyOrIndex,val){
        this.log('JSTable.setColTitle: Checking index type.','info');
        if(typeof colKeyOrIndex === 'number'){
            this.log('JSTable.setColTitle: Column index is given.','info');
            this.log('JSTable.setColTitle: Checking index validity.','info');
            if(colKeyOrIndex >= 0 && colKeyOrIndex < this.cols()){
                this.log('JSTable.setColTitle: Valid index.','info');
                this.log('JSTable.setColTitle: Updating title.','info');
                this.obj.cols[colKeyOrIndex].title = val;
                if(this.hasHeader()){
                    this.log('JSTable.setColTitle: Updating header.','info');
                    this.header.t_h_row.children[colKeyOrIndex].innerHTML = val;
                }
                if(this.hasFooter()){
                    this.log('JSTable.setColTitle: Updating footer.','info');
                    this.footer.t_f_row.children[colKeyOrIndex].innerHTML = val;
                }
                this.log('JSTable.setColTitle: Returing true.','info');
                return true;
            }
            else{
                this.log('JSTable.setColTitle: Invalid column index: '+colKeyOrIndex,'warning',true);
            }
        }
        else if(typeof colKeyOrIndex === 'string'){
            this.log('JSTable.setColTitle: Column key is given.','info');
            this.log('JSTable.setColTitle: Getting column index.','info');
            var colIndex = this.getColIndex(colKeyOrIndex);
            if(colIndex !== -1){
                this.log('JSTable.setColTitle: Column index = '+colIndex,'info');
                this.log('JSTable.setColTitle: Updating title.','info');
                this.obj.cols[colIndex].title = val;
                if(this.hasHeader()){
                    this.log('JSTable.setColTitle: Updating header.','info');
                    this.header.t_h_row.children[colIndex].innerHTML = val;
                }
                if(this.hasFooter()){
                    this.log('JSTable.setColTitle: Updating footer.','info');
                    this.footer.t_f_row.children[colIndex].innerHTML = val;
                }
                this.log('JSTable.setColTitle: Returing true.','info');
                return true;
            }
            else{
                this.log('JSTable.setColTitle: Invalid column key: '+colKeyOrIndex,'warning',true);
            }
        }
        this.log('JSTable.setColTitle: Reurning false.','info');
        return false;
    },
    /**
     * Returns all td elements of a column given its index or key.
     * @param {String|Number} colKeyOrIndex The index of the column or its key.
     * @returns {Array} An array that contains all column td elements.
     */
    getUIColumn:function(colKeyOrIndex){
        var cells = [];
        this.log('JSTable.getUIColumn: Checking index type.','info');
        if(typeof colKeyOrIndex === 'number'){
            this.log('JSTable.getUIColumn: An index is given.','info');
            this.log('JSTable.getUIColumn: Checking index validity.','info');
            if(colKeyOrIndex >= 0 && colKeyOrIndex < this.cols()){
                this.log('JSTable.getUIColumn: Valid index.','info');
                this.log('JSTable.getUIColumn: Extracting column cells','info');
                var vRows = this.visibleRows();
                for(var x = 0 ; x < vRows ; x++){
                    cells.push(this.t_body.children[x].children[colKeyOrIndex]);
                }
            }
            else{
                this.log('JSTable.getUIColumn: Invalid col index: '+colKeyOrIndex,'warning',true);
            }
        }
        else if(typeof colKeyOrIndex === 'string'){
            this.log('JSTable.getUIColumn: A key is given.','info');
            this.log('JSTable.getUIColumn: Getting column index.','info');
            var colIndex = this.getColIndex(colKeyOrIndex);
            if(colIndex !== -1){
                this.log('JSTable.getUIColumn: Col index = '+colIndex,'info');
                this.log('JSTable.getUIColumn: Extracting column cells','info');
                var vRows = this.visibleRows();
                for(var x = 0 ; x < vRows ; x++){
                    cells.push(this.t_body.children[x].children[colIndex]);
                }
            }
            else{
                this.log('JSTable.getColumn: Invalid col key: '+colKeyOrIndex,'warning');
            }
        }
        this.log('JSTable.getUIColumn: Returning cells array.','info');
        return cells;
    },
    /**
     * Returns an object that contains column information given its index or key.
     * @param {String|Number} colKeyOrIndex The index of the column or its key.
     * @returns {Object|undefined} An object that contains column information. If no 
     * column is found, the function will return undefined.
     */
    getColumn:function(colKeyOrIndex){
        this.log('JSTable.getColumn: Checking index type.','info');
        if(typeof colKeyOrIndex === 'number'){
            this.log('JSTable.getColumn: An index is given.','info');
            this.log('JSTable.getColumn: Checking index validity.','info');
            if(colKeyOrIndex >= 0 && colKeyOrIndex < this.cols()){
                this.log('JSTable.getColumn: Valid index.','info');
                this.log('JSTable.getColumn: Returning the column object.','info');
                return this.obj.cols[colKeyOrIndex];
            }
            else{
                this.log('JSTable.getColumn: Invalid col index: '+colKeyOrIndex,'warning',true);
            }
        }
        else if(typeof colKeyOrIndex === 'string'){
            this.log('JSTable.getColumn: A key is given.','info');
            this.log('JSTable.getColumn: Getting column index.','info');
            var colIndex = this.getColIndex(colKeyOrIndex);
            if(colIndex !== -1){
                this.log('JSTable.getColumn: Col index = '+colIndex,'info');
                this.log('JSTable.getColumn: Returning the column object.','info');
                return this.obj.cols[colIndex];
            }
            else{
                this.log('JSTable.getColumn: Invalid col key: '+colKeyOrIndex,'warning');
            }
        }
        this.log('JSTable.getColumn: Returning undefined.','info');
        return undefined;
    },
    /**
     * Returns a string that represents the datatype the column contains 
     * given its index or key.
     * @param {String|Number} colKeyOrIndex The index of the column or its key.
     * @returns {String|undefined} A string such as 'boolean' or 'string'. If no 
     * column is found, the function will return undefined.
     */
    getColDataType:function(colIndexOrKey){
        var col = this.getColumn(colIndexOrKey);
        if(col !== undefined){
            return col.type;
        }
        this.log('JSTable.getColDataType: Invalid column name or index: '+colIndexOrKey,'warning',true);
        return undefined;
    },
    /**
     * Returns the default value of the column.
     * @param {String|Number} colKeyOrIndex The index of the column or its key.
     * @returns {undefined|Mixed} The default value of the column. If no column is 
     * found, the function will return undefined.
     */
    getColDefault:function(colKeyOrIndex){
        var col = this.getColumn(colKeyOrIndex);
        if(col !== undefined){
            return col.default;
        }
        this.log('JSTable.getColDefault: No such column: '+colKeyOrIndex+'.','warning',true);
        return undefined;
    },
    /**
     * Sets the default value of a column.
     * @param {String|Number} colKeyOrIndex The index of the column or its key.
     * @param {Object} val An object that has two attributes, one is 'type' which 
     * contains the datatype of the value and the other is 'val' which is the value 
     * to set.
     * @returns {Boolean} True if updated.
     */
    setColDefault:function(colKeyOrIndex,val={type:'string',val:''}){
        if(this.hasCol(colKeyOrIndex)){
            if(typeof val === 'object'){
                var colDataType = this.getColDataType(colKeyOrIndex);
                if(val.type === colDataType){
                    this.getColumn(colKeyOrIndex).default = val.val;
                    return true;
                }
                else{
                    this.log('JSTable.setColDefault: Column datatype does not match provided value type.','info');
                }
            }
            return false;
        }
        this.log('JSTable.setColDefault: No such column: '+colKeyOrIndex,'info');
        return false;
    },
    /**
     * Adds new row to the table.
     * @param {Object} data An object that represents the row.
     * @param {Boolean} storeData If set to true, the data will be kept in the 
     * table (inside obj.data array). Else if false, A row will be added to the UI only. 
     * @returns {undefined}
     */
    addRow:function(data={}){
        this.log('JSTable.addRow: Checking if data is an object.','info');
        if(typeof data === 'object'){
            this.log('JSTable.addRow: Getting visible rows number.','info');
            var vRows = this.visibleRows();
            this.log('JSTable.addRow: Checking attribute \'hidden\' of the row.','info');
            if(typeof data.hidden !== 'boolean'){
                this.log('JSTable.addRow: Attribute \'hidden\' of the row is not set. True is used.','warning');
                data.hidden = false;
            }
            this.log('JSTable.addRow: Setting row index.','info');
            if(data.hidden === true){
                this.log('JSTable.addRow: Hidden row.','info');
                data['row-index'] = -1;
            }
            else if(this.obj.paginate === true){
                this.log('JSTable.addRow: Pagination is enabled.','info');
                data['row-index'] = vRows;
            }
            else{
                this.log('JSTable.addRow: Pagination is not enabled.','info');
                data['row-index'] = vRows;
            }
            this.log('JSTable.addRow: Row index = '+data['row-index'],'info');
            this.log('JSTable.addRow: Checking data keys.','info');
            this.log('JSTable.addRow: Printing row that will be inserted.','info');
            this.log(data);
            var keys = Object.keys(data);
            var columns = [];
            //first, extract the data that can be inserted.
            this.log('JSTable.addRow: Extracting data to insert','info');
            for(var x = 0 ; x < keys.length ; x++){
                this.log('JSTable.addRow: Key = '+keys[x],'info');
                //if(keys[x] !== 'show' && keys[x] !== 'row-index' && keys[x] !== 'hidden'){
                    var col = this.getColumn(keys[x]);
                    if(col === undefined){
                        this.log('JSTable.addRow: Key \''+keys[x]+'\' is not a column in the table.','warning');
                    }
                    else{
                        columns.push(
                                {
                                    key:keys[x],
                                    index:col.index,
                                    datatype:col.type,
                                    hidden:col.hidden
                                });
                    }
                //}
                //else{
                //    this.log('JSTable.addRow: Skipping column','info');
                //}
            }
            this.log('JSTable.addRow Number of cols with data = '+columns.length,'info');
            if(columns.length !== 0){
                //check missing attributes 
                this.log('JSTable.addRow: Checking for missing values.','info');
                var colKeys = this.getColsKeys();
                for(var x = 0 ; x < colKeys.length ; x++){
                    var colKey = colKeys[x];
                    if(colKey !== 'row-index'){
                        var hasValue = false;
                        for(var y = 0 ; y < columns.length ; y++){
                            if(columns[y].key === colKey){
                                this.log('JSTable.addRow: Key \''+colKey+'\' has a value.','info');
                                hasValue = true;
                                break;
                            }
                        }
                        if(!hasValue){
                            this.log('JSTable.addRow: Key \''+colKey+'\' has no value. Using default.','warning',true);
                            var col = this.getColumn(colKey);
                            columns.push({key:colKey,index:col.index,datatype:col.type,hidden:col.hidden});
                            data[colKey] = col.default;
                        }
                    }
                }
                this.log('JSTable.addRow: Printing columns array.','info');
                this.log(columns);
                this.log('JSTable.addRow: Printing row to add.','info');
                this.log(data);
                this.log('JSTable.addRow: Storing data object.','info');
                this.obj.data.push(data);
                if(data.hidden === false){
                    this.obj.events['new-row'] = data;
                    this.obj.events.columns = columns;
                    this.obj.events.datatable = this;
                    this.log('JSTable.addRow: Firing afterrowstored event.','info');
                    this.obj.events.afterrowstored();
                    this.log('JSTable.addRow: Event finished.','info');
                    delete this.obj.events['new-row'];
                    delete this.obj.events.columns;
                    return true;
                }
            }
            else{
                this.log('JSTable.addRow: No row added. No data on the object','warning',true);
            }
        }
        else{
            this.log('JSTable.addRow: Given data is not an object.','warning',true);
        }
        return false;
    },
    /**
     * Sets a function to call after a row is added.
     * @param {Function} func The function to set.
     * @returns {undefined}
     */
    setOnRowAdded:function(func){
        if(typeof func === 'function'){
            this.obj.events.onrowadded = func;
        }
        else{
            this.log('JSTable.setOnRowAdded: Provided parameter is not a function.','warning',true);
        }
    },
    /**
     * Checks if the column is printable or not.
     * @param {String|Number} colKeyOrIndex The index of the column or its key.
     * @returns {Boolean} True if the column will appear in the print area.
     */
    isColPrintable:function(colKeyOrIndex){
        var col = this.getColumn(colKeyOrIndex);
        if(col !== undefined){
            return col['printable'] !== false;
        }
        return false;
    },
    /**
     * Returns the sum of visible columns widthes in percentage.
     * @returns {Number} The sum of visible columns widthes in percentage.
     */
    colsWidth:function(){
        var width = 0;
        for(var x = 0 ; x < this.cols() ; x++){
            var col = this.getColumn(x);
            if(col.hidden === false || col.hidden === undefined){
                width += col.width;
            }
        }
        return width;
    },
    /**
     * Adds new column to the table.
     * @param {Object} col An object that represents the column. 
     * Each object can have the following attributes:
     * <ul>
     * <li><b>key</b>: A unique name for the column. The key is used in case of adding new 
     * row to the table.</li>
     * <li><b>title</b>: A text to show in the header and the footer of the column.</li>
     * <li><b>width</b>: The width of the column in percentage.</li>
     * <li><b>sortable</b>: A boolean value. If set to true, the rows of the column will 
     * be sortable. Default is false.</li>
     * <li><b>search-enabled</b>: A boolean value. if set to true, the user will be able to search 
     * the data on the column. Default is false.</li>
     * <li><b>printable</b>: A boolean value. If set to true, The column will appear in case 
     * of printing. Default is true.</li>
     * </ul>
     * @returns {undefined}
     */
    addColumn:function(col={}){
        this.log('JSTable.addColumn: Checking if parameter \'col\' is an object.','info');
        if(typeof col === 'object'){
            this.log('JSTable.addColumn: Checking if attribute \'key\' is set.','info');
            if(col.key !== undefined){
                col.key = ''+col.key;
                this.log('JSTable.addColumn: Checking if attribute \'key\' is not an empty string.','info');
                if(col.key.length > 0){
                    this.log('JSTable.addColumn: Column Key: '+col.key,'info');
                    this.log('JSTable.addColumn: Checking if column already in the table or not.','info');
                    if(!this.hasCol(col.key)){
                        this.log('JSTable.addColumn: Checking print state of the column','info');
                        if(col.printable === undefined){
                            col.printable = true;
                        }
                        col.index = this.cols();
                        this.log('JSTable.addColumn: Defining attribute \'index\' [index = '+col.index+'].','info');
                        this.log('JSTable.addColumn: Creating \'col\' HTML element.','info');
                        var colSetCol = document.createElement('col');
                        this.log('JSTable.addColumn: Setting column span.','info');
                        colSetCol.span = '1';
                        this.log('JSTable.addColumn: Checking attribute \'width\' of the column.','info');
                        if(col.width !== undefined){
                            if(col.width <= 100 && col.width > 0){
                                colSetCol.width = col.width+'%';
                                this.log('JSTable.addColumn: Column width: '+colSetCol.width+'.','info');
                            }
                            else{
                                this.log('JSTable.addColumn: Invalid Column width: '+col.width+'. 10% is used as default.','warning',true);
                                colSetCol.width = '10%';
                                col.width = 10;
                            }
                        }
                        else{
                            this.log('JSTable.addColumn: Column width not specifyed. 10% is used as default.','warning','info');
                            colSetCol.width = '10%';
                            col.width = 10;
                        }
                        this.log('JSTable.addColumn: Appending \'col\' element to the \'colgroup\' element.','info');
                        this.col_set.appendChild(colSetCol);
                        this.log('JSTable.addColumn: Checking if table has header.','info');
                        if(this.hasHeader()){
                            this.log('JSTable.addColumn: Creating \'th\' HTML element.','info');
                            var hCell = document.createElement('th');
                            this.log('JSTable.addColumn: Checking attribute \'sortable\' of the column.','info');
                            if(col.sortable === true){
                                this.log('JSTable.addColumn: It is set to \'true\'.','info');
                                this.log('JSTable.addColumn: Setting attribute \'role\' of \'th\' element to \'sort-button\'.','info');
                                hCell.setAttribute('role','sort-button');
                                hCell.dataTable = this;
                                var colNum = this.getCols().length !== 0 ? this.getCols().length : 0;
                                this.log('JSTable.addColumn: Getting column number [number = '+colNum+']','info');
                                this.log('JSTable.addColumn: Initializing sort event \'th.onclick\'.','info');
                                hCell.onclick = function(){
                                    this.dataTable.sort(colNum);
                                };
                            }
                            this.log('JSTable.addColumn: Checking attribute \'title\' of the column.','info');
                            if(col.title !== undefined){
                                this.log('JSTable.addColumn: Setting \'th\' inner html to \''+col.title+'\'.','info');
                                hCell.innerHTML = col.title;
                            }
                            else{
                                hCell.innerHTML = 'Col-'+this.getCols().length;
                                col.title = hCell.innerHTML;
                                this.log('JSTable.addColumn: The atribute \'title\' is undefined. Column title set to \''+col.title+'\'.','warning',true);
                            }
                            this.log('JSTable.addColumn: Adding \'th\' element to header row.','info');
                            this.header.t_h_row.appendChild(hCell);
                        }
                        this.log('JSTable.addColumn: Checking if table has footer.','info');
                        if(this.hasFooter()){
                            this.log('JSTable.addColumn: Creating \'th\' HTML element for the footer.','info');
                            var fCell = document.createElement('th');
                            fCell.innerHTML = col.title;
                            this.log('JSTable.addColumn: Adding \'th\' element to footer row.','info');
                            this.footer.t_f_row.appendChild(fCell);
                        }
                        this.log('JSTable.addColumn: Checking if column is printable or not.','info');
                        if(col.printable === false){
                            this.log('JSTable.addColumn: It is not printable.','info');
                            colSetCol.className = 'no-print';
                            if(this.hasHeader()){
                                hCell.className = 'no-print';
                            }
                            if(this.hasFooter()){
                                fCell.className = 'no-print';
                            }
                        }
                        else{
                            this.log('JSTable.addColumn: It is printable.','info');
                        }
                        this.log('JSTable.addColumn: Checking if the column is hidden or not.','info');
                        if(col.hidden === true){
                            this.log('JSTable.addColumn: It is hidden.','info');
                            colSetCol.className = colSetCol.className+' hidden';
                            if(this.hasHeader()){
                                hCell.className = hCell.className+' hidden';
                            }
                            if(this.hasFooter()){
                                fCell.className = fCell.className+' hidden';
                            }
                        }
                        else{
                            this.log('JSTable.addColumn: It is not hidden.','info');
                        }
                        this.log('JSTable.addColumn: Checking if search in the column is enabled.','info');
                        if(col['search-enabled'] === true){
                            this.log('JSTable.addColumn: Checking if search and selective serach is enabled.','info');
                            if(this.isSearchEnabled() && this.isSelectiveSearchEnabled()){
                                this.log('JSTable.addColumn: Creating \'option\' element for selective search.','info');
                                var o = document.createElement('option');
                                o.innerHTML = col.title;
                                o.value = col.key;
                                this.log('JSTable.addColumn: Appending \'option\' element to select element.','info');
                                this.col_select.appendChild(o);
                            }
                        }
                        this.log('JSTable.addColumn: Checking attribute \'type\'.','info');
                        if(JSTable.SUPPORTED_DATATYPES.indexOf(col.type) === -1){
                            this.log('JSTable.addColumn: Unsupported datatype: '+col.type+'. Default is used (string)','warning',true);
                            col.type = 'string';
                            if(col.default !== undefined){
                                col.default = ''+col.default;
                            }
                        }
                        this.log('JSTable.addColumn: Checking attribute \'default\'.','info');
                        if(col.default === undefined){
                            this.log('JSTable.addColumn: No default value for the column is provided.','warning',true);
                            if(col.type === 'string'){
                                col.default = '-';
                                this.log('JSTable.addColumn: \'-\' is used as default value.','info',true);
                            }
                            else if(col.type === 'number'){
                                col.default = 0;
                                this.log('JSTable.addColumn: \'0\' is used as default value.','info',true);
                            }
                            else if(col.type === 'boolean'){
                                col.default = false;
                                this.log('JSTable.addColumn: \'false\' is used as default value.','info',true);
                            }
                        }
                        this.log('JSTable.addColumn: Appending column to set of columns.','info,');
                        this.obj.cols.push(col);
                        this.log('JSTable.addColumn: Adding empty cells for the column.','info');
                        for(var x = 0 ; x < this.rows() ; x++){
                            this.getData()[x][col.key] = col.default;
                            var cell = document.createElement('td');
                            cell.innerHTML = col.default;
                            this.t_body.children[x].appendChild(cell);
                        }
                        this.log('JSTable.addColumn: New column added.',true);
                        this.validateDataState();
                        this.log('JSTable.addColumn: Firing oncoladded.',true);
                        if(typeof this.obj.events.oncoladded === 'function'){
                            this.obj.events.datatable = this;
                            this.obj.events['col-data'] = col;
                            this.obj.events.col = colSetCol;
                            if(this.hasFooter()){
                                this.obj.events.fcell = fCell;
                            }
                            if(this.hasHeader()){
                                this.obj.events.hcell = hCell;
                            }
                            this.obj.events.oncoladded();
                            delete this.obj.events['col-data'];
                            delete this.obj.events.col;
                            delete this.obj.events.fcell;
                            delete this.obj.events.hcell;
                            this.log('JSTable.addColumn: Event completed.',true);
                        }
                        else{
                            this.log('JSTable.addColumn: No event is fired.',true);
                        }
                        
                    }
                    else{
                        this.log('JSTable.addColumn: A column was already added with key = '+col.key,'warning',true);
                        this.log('JSTable.addColumn: No column is added.','info');
                    }
                }
                else{
                    this.log('JSTable.addColumn: Invalid column key: '+col.key,'warning',true);
                    this.log('JSTable.addColumn: No column is added.','info');
                }
            }
            else{
                this.log('JSTable.addColumn: The attribute \'key\' is missing.','warning',true);
                this.log('JSTable.addColumn: No column is added.','info');
            }
        }
        else{
            this.log('JSTable.addColumn: The given parameter is not an object.','warning',true);
            this.log('JSTable.addColumn: No column is added.','info');
        }
        this.log('JSTable.addColumn: Return back','info');
    },
    /**
     * Sets a function to call after a column is added.
     * @param {Function} func The function to set.
     * @returns {undefined}
     */
    setOnColAdded:function(func){
        if(typeof func === 'function'){
            this.obj.events.oncoladded = func;
        }
        else{
            this.log('JSTable.setOnRowRemoved: Given parameter is not a function.','warning',true);
        }
    },
    /**
     * Checks if a given column is in the table or not.
     * @param {type} colKeyOrIndex
     * @returns {Boolean}
     */
    hasCol:function(colKeyOrIndex){
        if(typeof colKeyOrIndex === 'string'){
            var cols = this.getCols();
            for(var x = 0 ; x < cols.length ; x++){
                if(cols[x].key === colKeyOrIndex){
                    return true;
                }
            }
        }
        else if(typeof colKeyOrIndex === 'number'){
            if(this.cols() > 0){
                colKeyOrIndex = Number.parseInt(colKeyOrIndex);
                return colKeyOrIndex >= 0 && colKeyOrIndex < this.cols();
            }
        }
        return false;
    },
    /**
     * Returns the data that is used by the table.
     * @returns {Array} An array of objects that contains all table data.
     */
    getData:function(){
        return this.obj.data;
    },
    /**
     * Returns the number of visible rows.
     * @returns {Number} The number of visible rows.
     */
    visibleRows:function(){
        if((this.t_body.children[0] === this.noDataRow || this.t_body.children[0] === undefined) && this.t_body.children.length <= 1 ){
            this.log('JSTable.visibleRows: returning 0','info');
            return 0;
        }
        this.log('JSTable.visibleRows: returning '+this.t_body.children.length,'info');
        return this.t_body.children.length;
    },
    /**
     * Returns the total number of rows in the table.
     * @returns {Number} The total number of rows in the table.
     */
    rows:function(){
        return this.getData().length;
    },
    /**
     * Returns the number of columns in the table.
     * @returns {Number} The number of columns in the table.
     */
    cols:function(){
        return this.getCols().length;
    },
    /**
     * Returns the number of visible columns.
     * @returns {Number} The number of visible columns.
     */
    visibleCols:function(){
        var count = 0;
        var cols = this.getCols();
        for(var x = 0 ; x < cols.length ; x++){
            if(cols[x].hidden !== true){
                count++;
            }
        }
        return count;
    },
    /**
     * Returns an array of objects. Each object represents a row.
     * @returns {Array} An array of objects. Each object represents a row.
     */
    getCols:function(){
        return this.obj.cols;
    },
    /**
     * Checks if the table has a footer or not.
     * @returns {Boolean} True if has a footer. False otherwise.
     */
    hasFooter:function(){
        if(this.obj['footer'] === true){
            return true;
        }
        return false;
    },
    /**
     * Checks if the table has a header or not.
     * @returns {Boolean} True if has a header. False otherwise.
     */
    hasHeader:function(){
        if(this.obj['header'] === true){
            return true;
        }
        return false;
    },
    /**
     * Returns the ID of the element that will contain the table.
     * @returns {String} The ID of the element that will contain the table.
     */
    getParentHTMLID:function(){
        return this.obj['parent-html-id'];
    },
    /**
     * Checks if the table is appended to HTML element or not.
     * @returns {Boolean} True if it is appended. False otherwise.
     */
    hasParent:function(){
        return this.obj['has-parent'] === true;
    },
    /**
     * Returns an HTML element that represents the whole table along its controls.
     * @returns {HTMLElement}
     */
    toDOMElement:function(){
        return this.container;
    },
    /**
     * Appends the table to its parent. The ID of the parent must set first.
     * @returns {undefined}
     */
    attach:function(){
        if(this.hasParent() !== true){
            var parent = document.getElementById(this.getParentHTMLID());
            if(parent !== null){
                parent.appendChild(this.toDOMElement());
                this.log('JSTable.attach: Updating the property \'has-parent\'.','info');
                this.obj['has-parent'] = true;
            }
            else{
                this.log('JSTable.attach: No element was fount with ID = '+this.getParentHTMLID(),'warning',true);
            }
        }
        else{
            this.log('JSTable.attach: Already appended to element with ID = '+this.getParentHTMLID(),'info',true);
        }
    },
    /**
     * Returns an array that contains all header cells.
     * @returns {Array} An array that contains all header cells.
     */
    getHeaders:function(){
        return this.t_header.children[0].children;
    },
    /**
     * Returns a header cell given column index.
     * @param {Number} colIndex The index of the column.
     * @returns {HTMLElement}
     */
    getHeader:function(colIndex){
        return this.getHeaders()[colIndex];
    },
    /**
     * Returns an array that contains All visible rows of the table.
     * @returns {Array}
     */
    getRows:function(){
        return this.t_body.children;
    },
    /**
     * Returns an array that contains visible columns keys.
     * @returns {Array} An array that contains visible columns keys.
     */
    getVisibleColsKeys:function(){
        var cols = this.getCols();
        var rtVal = [];
        for(var x = 0 ; x < cols.length ; x++){
            if(cols[x].hidden !== true){
                rtVal.push(cols[x].key);
            }
        }
        return rtVal;
    },
    /**
     * Returns an array that contains search columns keys.
     * @returns {Array} An array that contains search columns keys.
     */
    getSearchColsKeys:function(){
        var cols = this.getCols();
        var rtVal = [];
        for(var x = 0 ; x < cols.length ; x++){
            if(cols[x].hidden !== true && cols[x]['search-enabled'] === true){
                rtVal.push(cols[x].key);
            }
        }
        return rtVal;
    },
    /**
     * Returns an array that contains search columns keys.
     * @returns {Array} An array that contains search columns keys.
     */
    getColsKeys:function(){
        var cols = this.getCols();
        var rtVal = [];
        for(var x = 0 ; x < cols.length ; x++){
            rtVal.push(cols[x].key);
        }
        return rtVal;
    },
    /**
     * Sort a column given its number.
     * @param {Number} sourceCol The index of the column.
     * @returns {undefined}
     */
    sort:function(sourceCol){
        this.log('JSTable.sort: Sorting by col number '+sourceCol);
        if(sourceCol >= 0 && sourceCol < this.cols()){
            var sortType = this.updateHeaders(sourceCol);
            this.log('JSTable.sort: Sort Type: '+sortType,'info');
            var dataToSort = extractColData(sourceCol,this.getData(),this.getColsKeys());
            this.log('Datatable.sort: Printing Data that will be sorted','info');
            this.log(dataToSort);
            if(sortType === 'up'){
                var sorted = insertionSort(dataToSort,true);
            }
            else if(sortType === 'down'){
                var sorted = insertionSort(dataToSort,false);
            }
            this.log('Datatable.sort: Printing sorted Data.','info');
            this.log(sorted);
            var colKeys = this.getColsKeys();
            this.log('JSTable.sort: Updating table.','info');
            for(var x = 0 ; x < sorted.length ; x++){
                this.obj.data[x] = sorted[x];
            }
            this.log('JSTable.sort: Refreshing table.','info');
            if(this.isSearchEnabled()){
                this.search_input.oninput();
            }
            else{
                this.obj.events.afterrowcountchanged();
            }
            this.log('JSTable.sort: Sorting finished.','info');
        }
        else{
            this.log('JSTable.sort: Invalid sort column index: '+sourceCol,'warning');
        }
    },
    /**
     * Update headers after sorting.
     * @param {Number} childToSkip Index of header which was used for sorting.
     * @returns {undefined}
     */
    updateHeaders:function(childToSkip){
        var sortType = 'up';
        for(var x = 0 ; x < this.header.t_header.children[0].children.length ; x++){
            var cell = this.header.t_header.children[0].children[x];
            if(cell.attributes.role !== undefined){
                var cellRole = cell.attributes.role.value;
                if(x === childToSkip){
                    if(cellRole === 'sort-up'){
                        sortType = 'up';
                        cell.attributes.role.value = 'sort-down';
                    }
                    else{
                        cell.attributes.role.value = 'sort-up';
                        sortType = 'down';
                    }
                    continue;
                }
                if(this.header.t_header.children[0].children[x].attributes.role !== undefined){
                    if(cellRole === 'sort-up' || cellRole === 'sort-down'){
                        cell.attributes.role.value = 'sort-button';
                    }
                }
            }
        }
        return sortType;
    }
});

function extractColData(colNum,tableDataArr, colKeys){
    var dataToSort = [];
    for(var x = 0 ; x < tableDataArr.length; x++){
        var copy = Object.assign({},tableDataArr[x]);
        copy.data = tableDataArr[x][colKeys[colNum]];
        dataToSort.push(copy);
    }
    return dataToSort;
}
/**
 * Convert a string into a number.
 * @param {type} str
 * @returns {Number}
 */
function strToNum(str){
    var num = Number.parseFloat(str);
    if(!Number.isNaN(num)){
        return num;
    }
    num = 0;
    for(var x = 0 ; x < str.length ; x++){
        var chAsNum = str.charCodeAt(x);
        if(chAsNum >= 48 && chAsNum <= 57){
            chAsNum -= 48;
        }
        num += chAsNum;
    }
    return num;
}
function quickSort(A, lo, hi,ascending=true){
    if(lo < hi){
        if(ascending === true){
            var splitLoc = quickSort_h_1(A, lo, hi);
        }
        else{
            var splitLoc = quickSort_h_2(A, lo, hi);
        }
        A = quickSort(A, lo, splitLoc - 1,ascending);
        A = quickSort(A, splitLoc + 1, hi,ascending);
    }
    return A;
}
function quickSort_h_2(A, lo, hi){
    var pivot = A[hi];
    var wall = lo - 1;
    if(typeof pivot === 'object'){
        if(pivot.data !== undefined){
            for(var current = wall + 1 ; current < hi ; current++){
                var currentObj = A[current];
                if(typeof currentObj === 'object'){
                    if(currentObj.data !== undefined){
                        if(stringCompare(currentObj.data,pivot.data) === 1){
                            wall++;
                            var tmp = A[current];
                            A[current] = A[wall];
                            A[wall] = tmp;
                        }
                    }
                    else{
                        throw new Error('The attribute \'data\' is missing from the object.');
                    }
                }
                else{
                    throw new Error('Dataset must contain objects only.');
                }
            }
        }
        else{
            throw new Error('The attribute \'data\' is missing from the object.');
        }
    }
    else{
        throw new Error('Dataset must contain objects only.');
    }
    wall++;
    var tmp = A[wall];
    A[wall] = pivot;
    A[hi] = tmp;
    return wall;
}
function quickSort_h_1(A, lo, hi){
    var pivot = A[hi];
    var wall = lo - 1;
    if(typeof pivot === 'object'){
        if(pivot.data !== undefined){
            for(var current = wall + 1 ; current < hi ; current++){
                var currentObj = A[current];
                if(typeof currentObj === 'object'){
                    if(currentObj.data !== undefined){
                        if(stringCompare(currentObj.data,pivot.data) === -1){
                            wall++;
                            var tmp = A[current];
                            A[current] = A[wall];
                            A[wall] = tmp;
                        }
                    }
                    else{
                        throw new Error('The attribute \'data\' is missing from the object.');
                    }
                }
                else{
                    throw new Error('Dataset must contain objects only.');
                }
            }
        }
        else{
            throw new Error('The attribute \'data\' is missing from the object.');
        }
    }
    else{
        throw new Error('Dataset must contain objects only.');
    }
    wall++;
    var tmp = A[wall];
    A[wall] = pivot;
    A[hi] = tmp;
    return wall;
}
function insertionSort(arr,ascending=true){
    if(ascending === true){
        var cond = 1;
    }
    else{
        var cond = -1;
    }
    for (var i = 1; i < arr.length; ++i){
        if(typeof arr[i] === 'object'){
            var key = arr[i];
            if(key.data !== undefined){
                var j = i - 1;
                while (j >= 0 &&  stringCompare(arr[j].data,key.data) === cond){
                    arr[j + 1] = arr[j];
                    j = j - 1;
                }
                arr[j + 1] = key;
            }
            else{
                throw new Error('The attribute \'data\' is missing from the object.');
            }
        }
        else{
            throw new Error('Dataset must contain objects only.');
        }
    }
    return arr;
}
function updateHeaders(otherHeaders,childToSkip){
    for(var x = 0 ; x < otherHeaders.length ; x++){
        if(x === childToSkip){
            continue;
        }
        var cell = otherHeaders[x];
        if(cell.attributes.role !== undefined){
            var cellRole = cell.attributes.role.value;
            if(cellRole === 'sort-up' || cellRole === 'sort-down'){
                cell.attributes.role.value = 'sort-button';
            }
        }
    }
}
/**
 * Compare two strings.
 * @param {type} str1
 * @param {type} str2
 * @returns {Number} 0 if the two strings are the same. -1 if 
 * the first string is less. 1 if the first string is greater.
 */
function stringCompare(str1,str2){
    if(str1 === undefined){
        console.warn('Parameter 1 (First String) is undefined.');
    }
    if(str2 === undefined){
        console.warn('Parameter 2 (Second String) is undefined.');
    }
    var str1AsNum = Number.parseFloat(str1);
    var str2AsNum = Number.parseFloat(str2);
    if(!Number.isNaN(str1AsNum) && !Number.isNaN(str1AsNum)){
        if(str1AsNum > str2AsNum){
            return 1;
        }
        else if(str1AsNum < str2AsNum){
            return -1;
        }
        else{
            return 0;
        }
    }
    str1 = ''+str1;
    str2 = ''+str2;
    if(str1.length > 0){
        if(str2.length > 0){
            if(str1.length > str2.length){
                var index = 0;
                while(index !== str2.length){
                    if(str1.charCodeAt(index) > str2.charCodeAt(index)){
                        return 1;
                    }
                    else if(str1.charCodeAt(index) < str2.charCodeAt(index)){
                        return -1;
                    }
                    index++;
                }
                return 1;
            }
            else if(str1.length < str2.length){
                var index = 0;
                while(index !== str1.length){
                    if(str1.charCodeAt(index) > str2.charCodeAt(index)){
                        return 1;
                    }
                    else if(str1.charCodeAt(index) < str2.charCodeAt(index)){
                        return -1;
                    }
                    index++;
                }
                return -1;
            }
            else{
                var index = 0;
                while(index !== str1.length){
                    if(str1.charCodeAt(index) > str2.charCodeAt(index)){
                        return 1;
                    }
                    else if(str1.charCodeAt(index) < str2.charCodeAt(index)){
                        return -1;
                    }
                    index++;
                }
                return 0;
            }
        }
    }
    return 0;
}
Object.defineProperties(JSTable,{
    createRow:{
        value:function(tableCols,data){
            var row = document.createElement('tr');
            for(var x = 0 ; x < tableCols.length ; x++){
                var col = tableCols[x];
                if(col.key !== 'show'){
                    var cell = document.createElement('td');
                    if(col.printable === false){
                        cell.className = 'no-print';
                    }
                    if(col.hidden === true){
                        cell.className = cell.className+' hidden';
                    }
                    if(typeof data[col.key] !== undefined){
                        cell.innerHTML = data[col.key];
                    }
                    else{
                        cell.innerHTML = col.default;
                    }
                    row.appendChild(cell);
                }
            }
            return row;
        }
    }
});