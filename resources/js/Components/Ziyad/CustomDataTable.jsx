import React, { useState } from 'react';
import PropTypes from 'prop-types';
import { DataGrid } from '@mui/x-data-grid';
import Paper from '@mui/material/Paper';
import { Box, Button, IconButton, InputAdornment, styled, TextField } from '@mui/material';


const defaultColumns = [
  { field: 'id', headerName: 'ID', width: 70 },
  { field: 'firstName', headerName: 'First name', width: 130 },
  { field: 'lastName', headerName: 'Last name', width: 130 },
  {
    field: 'age',
    headerName: 'Age',
    type: 'number',
    width: 90,
  },
  {
    field: 'fullName',
    headerName: 'Full name',
    description: 'This column has a value getter and is not sortable.',
    sortable: false,
    width: 160,
    valueGetter: (params) =>
      `${params.row.firstName || ''} ${params.row.lastName || ''}`,
  },
];

const StyledGridOverlay = styled('div')(({ theme }) => ({
    display: 'flex',
    flexDirection: 'column',
    alignItems: 'center',
    justifyContent: 'center',
    height: '100%',
    '& .no-rows-primary': {
      fill: '#3D4751',
      ...theme.applyStyles('light', {
        fill: '#AEB8C2',
      }),
    },
    '& .no-rows-secondary': {
      fill: '#1D2126',
      ...theme.applyStyles('light', {
        fill: '#E8EAED',
      }),
    },
  }));
  
  function CustomNoRowsOverlay() {
    return (
      <StyledGridOverlay>
        <svg
          xmlns="http://www.w3.org/2000/svg"
          fill="none"
          width={96}
          viewBox="0 0 452 257"
          aria-hidden
          focusable="false"
        >
          <path
            className="no-rows-primary"
            d="M348 69c-46.392 0-84 37.608-84 84s37.608 84 84 84 84-37.608 84-84-37.608-84-84-84Zm-104 84c0-57.438 46.562-104 104-104s104 46.562 104 104-46.562 104-104 104-104-46.562-104-104Z"
          />
          <path
            className="no-rows-primary"
            d="M308.929 113.929c3.905-3.905 10.237-3.905 14.142 0l63.64 63.64c3.905 3.905 3.905 10.236 0 14.142-3.906 3.905-10.237 3.905-14.142 0l-63.64-63.64c-3.905-3.905-3.905-10.237 0-14.142Z"
          />
          <path
            className="no-rows-primary"
            d="M308.929 191.711c-3.905-3.906-3.905-10.237 0-14.142l63.64-63.64c3.905-3.905 10.236-3.905 14.142 0 3.905 3.905 3.905 10.237 0 14.142l-63.64 63.64c-3.905 3.905-10.237 3.905-14.142 0Z"
          />
          <path
            className="no-rows-secondary"
            d="M0 10C0 4.477 4.477 0 10 0h380c5.523 0 10 4.477 10 10s-4.477 10-10 10H10C4.477 20 0 15.523 0 10ZM0 59c0-5.523 4.477-10 10-10h231c5.523 0 10 4.477 10 10s-4.477 10-10 10H10C4.477 69 0 64.523 0 59ZM0 106c0-5.523 4.477-10 10-10h203c5.523 0 10 4.477 10 10s-4.477 10-10 10H10c-5.523 0-10-4.477-10-10ZM0 153c0-5.523 4.477-10 10-10h195.5c5.523 0 10 4.477 10 10s-4.477 10-10 10H10c-5.523 0-10-4.477-10-10ZM0 200c0-5.523 4.477-10 10-10h203c5.523 0 10 4.477 10 10s-4.477 10-10 10H10c-5.523 0-10-4.477-10-10ZM0 247c0-5.523 4.477-10 10-10h231c5.523 0 10 4.477 10 10s-4.477 10-10 10H10c-5.523 0-10-4.477-10-10Z"
          />
        </svg>
        <Box sx={{ mt: 2 }}>Tidak Ada Data</Box>
      </StyledGridOverlay>
    );
}

const CustomDataTable = ({
    id = 'data-table',
    rows = [],
    columns = defaultColumns,
    checkbox = false,
    dynamicPageSize = true,
    pageSize = 5,
    onModal = '',
    toolbar = {
        search: true,
        export: {
            xlsx: true,
            csv: true,

        },
        import: {
            xlsx: true,
            csv: true
        },
        column: true,
        density: true
    },
    loading = false,
    rowSelect = {
        onChange: () => {},
        value: []
    },
    isRowSelectable = (params) => true,
    getRowId = (row) => row.id
}) => {
    return (
      <DataGrid
        getRowId={getRowId}
        rows={rows}
        columns={columns}
        pageSizeOptions={[5, 10, 25, 50, 100]}
        pagination
        // autosizeOnMount={loading}
        disableColumnMenu
        disableRowSelectionOnClick
        checkboxSelection={checkbox}
        loading={loading}
        isRowSelectable={isRowSelectable}
        initialState={{
          pagination: {
            paginationModel: {
              pageSize
            }
          }
        }}
        
        // slots={{
        //     // toolbar: () => (
        //     //     <GridToolbarContainer className='w-full'>
        //     //         {toolbar.search && (
        //     //             <GridToolbarQuickFilter variant='outlined' size={'small'} color={'primary'} placeholder='Cari disini' />
        //     //         )}
        //     //         {toolbar.column && (
        //     //             <GridToolbarColumnsButton  />
        //     //         )}
        //     //         {toolbar.density && (
        //     //             <GridToolbarDensitySelector />
        //     //         )}
        //     //     </GridToolbarContainer>
        //     // ),
        //     noRowsOverlay: CustomNoRowsOverlay,
        //     noResultsOverlay: CustomNoRowsOverlay
        // }}
        // slotProps={{
        //     loadingOverlay: {
        //         variant: 'skeleton',
        //         noRowsVariant: 'skeleton'
        //     }
        // }}
        
        onRowSelectionModelChange={rowSelect.onChange}
        rowSelectionModel={rowSelect.value}
      />
    )
}

export default CustomDataTable