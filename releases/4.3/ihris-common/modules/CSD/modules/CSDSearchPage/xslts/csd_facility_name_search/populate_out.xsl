<?xml version="1.0" encoding="UTF-8"?>   
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">     
  <xsl:output method="xml" version="1.0" encoding="UTF-8" indent="yes"/>     
  <xsl:template match="/">         
    <csd:careServicesRequest xmlns:csd="urn:ihe:iti:csd:2013" xmlns="urn:ihe:iti:csd:2013">             
      <function urn="urn:openhie.org:openinfoman-hwr:stored-function:facility_name_search">
	<requestParams>                      
	  <xsl:if test="/form/field[@name='entityID']">
	    <id><xsl:attribute name='entityID'><xsl:value-of select="/form/field[@name='entityID']"/></xsl:attribute></id>
	  </xsl:if>
	  <xsl:if test="/form/field[@name='primary_name']">
	    <primaryName><xsl:value-of select="/form/field[@name='primary_name']"/></primaryName>
	  </xsl:if>	  
	  <start><xsl:value-of select="/form/field[@name='start']"/></start>
	  <xsl:if test="/form/field[@name='max']">
	    <max><xsl:value-of select="/form/field[@name='max']"/></max>
	  </xsl:if>
	  <record>
	    <xsl:for-each select="/form/field[@name='updated']/value[1]">
	     <xsl:attribute name='status'><xsl:value-of select="year"/>-<xsl:value-of select="month"/>-<xsl:value-of select="day"/>T<xsl:value-of select="hour"/>:<xsl:value-of select="minute"/>:<xsl:value-of select="second"/></xsl:attribute>
	    </xsl:for-each>
	  </record>
	</requestParams>             
      </function>         
    </csd:careServicesRequest>     
  </xsl:template> 
</xsl:stylesheet>
