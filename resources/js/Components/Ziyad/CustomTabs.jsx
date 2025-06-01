import React, { Children } from 'react';
import PropTypes from 'prop-types';
import Tabs from '@mui/material/Tabs';
import Tab from '@mui/material/Tab';
import Box from '@mui/material/Box';

function CustomTabPanel(props) {
  const { children, value, index, ...other } = props;

  return (
    <div
      role="tabpanel"
      hidden={value !== index}
      id={`custom-tabpanel-${index}`}
      aria-labelledby={`custom-tab-${index}`}
      {...other}
    >
      {value === index && <Box sx={{ p: 0 }}>{children}</Box>}
    </div>
  );
}

CustomTabPanel.propTypes = {
  children: PropTypes.node,
  index: PropTypes.number.isRequired,
  value: PropTypes.number.isRequired,
};

export function CustomTabItem({ children }) {
  return <>{children}</>;
}

CustomTabItem.propTypes = {
  label: PropTypes.oneOfType([PropTypes.string, PropTypes.node]).isRequired,
  children: PropTypes.node,
};

export function CustomTabs({ children, centered = false }) {
  const [value, setValue] = React.useState(0);

  const handleChange = (event, newValue) => {
    setValue(newValue);
  };

  const tabs = Children.toArray(children).filter(
    (child) => child.type === CustomTabItem
  );

  return (
    <Box sx={{ width: '100%' }}>
      <Box sx={{ borderBottom: 1, borderTop: 1, borderColor: 'divider' }}>
        <Tabs
          value={value}
          onChange={handleChange}
          aria-label="custom tabs"
          variant="scrollable"
          scrollButtons="auto"
          // centered={centered}
        >
          {tabs.map((tab, index) => (
            <Tab
              key={index}
              label={tab.props.label}
              className="text-xs"
              id={`custom-tab-${index}`}
              aria-controls={`custom-tabpanel-${index}`}
            />
          ))}
        </Tabs>
      </Box>
      {tabs.map((tab, index) => (
        <CustomTabPanel key={index} value={value} index={index}>
          {tab.props.children}
        </CustomTabPanel>
      ))}
    </Box>
  );
}

CustomTabs.propTypes = {
  children: PropTypes.node.isRequired,
};